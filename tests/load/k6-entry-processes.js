/**
 * k6 Load Test Script — SMS Gathering & Order Night 2026
 * Target: Shared / Cloud Hosting Entry Processes & Concurrent Pharmacy Simulation
 *
 * Skrip ini dirancang khusus untuk menguji kapasitas "Entry Processes" (proses PHP konkurensi)
 * dari 72 apotek peserta gathering + 1 layar proyektor panggung TANPA membuat IP diblokir
 * oleh firewall (Imunify360 / Cloudflare / ModSecurity) atau merusak database produksi.
 *
 * CARA MENJALANKAN:
 * 1. Smoke Test (Aman, 5 user, cek koneksi):
 *    k6 run -e BASE_URL=https://domain-anda.com -e PROFILE=smoke tests/load/k6-entry-processes.js
 *
 * 2. Realistic Event (70 Apotek dengan auto-poll 8 detik + interaksi normal):
 *    k6 run -e BASE_URL=https://domain-anda.com -e PROFILE=realistic tests/load/k6-entry-processes.js
 *
 * 3. Flash Sale Spike (70 Apotek fokus 1 sesi pabrik bersamaan):
 *    k6 run -e BASE_URL=https://domain-anda.com -e PROFILE=spike tests/load/k6-entry-processes.js
 *
 * Catatan: Secara default skrip berjalan dalam mode READ-ONLY & PREVIEW (tidak membuat PO palsu).
 * Jika ingin ikut mengetes pembuatan PO ke database (di staging/lokal), tambahkan: -e ENABLE_CHECKOUT=true
 */

import http from 'k6/http';
import { check, group, sleep } from 'k6';
import { Counter, Rate, Trend } from 'k6/metrics';

// ============================================================================
// 1. KONFIGURASI PARAMETER & ENVIRONMENT
// ============================================================================
const BASE_URL = (__ENV.BASE_URL || 'http://localhost:8000').replace(/\/$/, '');
const PROFILE = (__ENV.PROFILE || 'realistic').toLowerCase(); // 'smoke' | 'realistic' | 'spike'
const ENABLE_CHECKOUT = (__ENV.ENABLE_CHECKOUT || 'false').toLowerCase() === 'true';

// Custom Metrics untuk memantau gejala limit Entry Processes (503 / 508 / 429)
const entryProcessLimitHits = new Counter('hosting_limit_hits_503_508_429');
const wafBlockHits = new Counter('waf_or_forbidden_hits_403');
const catalogDuration = new Trend('duration_catalog_ms', true);
const leaderboardDuration = new Trend('duration_leaderboard_ms', true);
const stageApiDuration = new Trend('duration_stage_api_ms', true);
const previewRewardDuration = new Trend('duration_preview_rewards_ms', true);
const errorRate = new Rate('failed_requests');

// ============================================================================
// 2. PROFIL SKENARIO BEBAN (SAFE FOR HOSTING)
// ============================================================================
const profiles = {
    // Profil 1: Smoke Test (5 Apotek selama 1 menit untuk pemanasan)
    smoke: {
        scenarios: {
            pharmacies: {
                executor: 'ramping-vus',
                startVUs: 1,
                stages: [
                    { duration: '15s', target: 5 },
                    { duration: '30s', target: 5 },
                    { duration: '15s', target: 0 },
                ],
                exec: 'pharmacyFlow',
            },
            stage_screen: {
                executor: 'constant-vus',
                vus: 1,
                duration: '1m',
                exec: 'stageProjectorFlow',
            },
        },
    },

    // Profil 2: Realistic Gathering (Bertahap naik ke 70 Apotek + 1 Layar Panggung)
    // Ramp-up 1.5 menit agar tidak dianggap serangan DDoS oleh Firewall Hosting
    realistic: {
        scenarios: {
            pharmacies: {
                executor: 'ramping-vus',
                startVUs: 2,
                stages: [
                    { duration: '30s', target: 20 }, // Tamu mulai registrasi
                    { duration: '45s', target: 45 }, // Separuh ballroom aktif
                    { duration: '45s', target: 70 }, // Full 70 apotek aktif bersamaan
                    { duration: '2m', target: 70 },  // Peak load (browsing + auto-poll 8 detik)
                    { duration: '30s', target: 0 },  // Pendinginan (ramp-down)
                ],
                exec: 'pharmacyFlow',
            },
            stage_screen: {
                executor: 'constant-vus',
                vus: 1,
                duration: '4m30s',
                exec: 'stageProjectorFlow',
            },
        },
    },

    // Profil 3: Flash Deal Spike (Lonjakan cepat ke 72 Apotek saat MC buka sesi 1 pabrik)
    spike: {
        scenarios: {
            pharmacies: {
                executor: 'ramping-vus',
                startVUs: 5,
                stages: [
                    { duration: '20s', target: 35 },
                    { duration: '25s', target: 72 }, // Seluruh 72 apotek buka katalog bersamaan
                    { duration: '1m30s', target: 72 },
                    { duration: '25s', target: 0 },
                ],
                exec: 'pharmacyFlow',
            },
            stage_screen: {
                executor: 'constant-vus',
                vus: 1,
                duration: '2m40s',
                exec: 'stageProjectorFlow',
            },
        },
    },
};

export const options = {
    scenarios: (profiles[PROFILE] || profiles.realistic).scenarios,

    // CIRCUIT BREAKER (PENGAMAN OTOMATIS):
    // Jika error (503/508/429/403) menyentuh > 10%, k6 akan OTOMATIS BERHENTI (abort)
    // dalam 10 detik agar hosting tidak terblokir atau reputasi IP rusak.
    thresholds: {
        http_req_duration: ['p(95)<4000'], // 95% request harus di bawah 4 detik
        failed_requests: [
            {
                threshold: 'rate<0.10', // Maksimal toleransi gagal 10%
                abortOnFail: true,      // LANGSUNG HENTIKAN TEST jika server mulai kewalahan
                delayAbortEval: '10s',
            },
        ],
        hosting_limit_hits_503_508_429: [
            {
                threshold: 'count<15',  // Jika muncul 15x error 508/503 (Entry Process Penuh), stop test!
                abortOnFail: true,
            },
        ],
        waf_or_forbidden_hits_403: [
            {
                threshold: 'count<5',   // Jika WAF/Firewall mulai memblokir (403), langsung stop!
                abortOnFail: true,
            },
        ],
    },
};

// ============================================================================
// 3. HELPER FUNGSI (CSRF, COOKIE, INERTIA HEADERS & INSPEKSI ERROR)
// ============================================================================

const MANUFACTURER_SLUGS = [
    'semua',
    'zenith-bufa',
    'graha-farma',
    'mepro',
    'interbat',
    'erita',
    'arto-farma',
    'yarindo',
    'dexa',
    'exeltis',
];

/**
 * Mengambil nilai XSRF-TOKEN dari Cookie Jar k6 untuk dikirim ke header X-XSRF-TOKEN Laravel
 */
function getXsrfToken() {
    const jar = http.cookieJar();
    const cookies = jar.cookiesForURL(BASE_URL);
    if (cookies['XSRF-TOKEN'] && cookies['XSRF-TOKEN'].length > 0) {
        return decodeURIComponent(cookies['XSRF-TOKEN'][0]);
    }
    return '';
}

/**
 * Mengekstrak versi asset Inertia dari halaman HTML awal (agar request polling X-Inertia ringan & tidak 409)
 */
function extractInertiaVersion(htmlBody) {
    if (!htmlBody) return '';
    const match = htmlBody.match(/&quot;version&quot;:&quot;([^&]+)&quot;|"version":"([^"]+)"/);
    return match ? (match[1] || match[2] || '') : '';
}

/**
 * Memeriksa status respons & mencatat apakah terkena limit Entry Process hosting
 */
function inspectHostingHealth(res, stepName) {
    const status = res.status;

    // 508 = Resource Limit Is Reached (cPanel / CloudLinux Entry Processes / LVE limit)
    // 503 = Service Unavailable (LiteSpeed / PHP-FPM max_children habis)
    // 502 = Bad Gateway (Worker timeout)
    // 429 = Too Many Requests (Rate limit)
    if (status === 508 || status === 503 || status === 502 || status === 429) {
        entryProcessLimitHits.add(1);
        errorRate.add(1);
        console.warn(`[PERINGATAN HOSTING] Step "${stepName}" mengembalikan HTTP ${status} (Kemungkinan Entry Processes / PHP Worker penuh)`);
        return false;
    }

    if (status === 403) {
        wafBlockHits.add(1);
        errorRate.add(1);
        console.error(`[BAHAYA WAF] Step "${stepName}" mendapat HTTP 403 Forbidden! Firewall hosting mungkin mulai membatasi IP.`);
        return false;
    }

    if (status === 0 || status >= 500) {
        errorRate.add(1);
        return false;
    }

    errorRate.add(0);
    return true;
}

/**
 * Jeda acak manusiawi (Think Time + Jitter) agar 70 user tidak memukul detik yang persis sama
 */
function humanSleep(minSec, maxSec) {
    const duration = minSec + Math.random() * (maxSec - minSec);
    sleep(duration);
}

// ============================================================================
// 4. SKENARIO UTAMA 1: SIMULASI 70 APOTEK (MOBILE GUEST)
// ============================================================================
export function pharmacyFlow() {
    // Setiap Virtual User (VU) memegang 1 kode apotek unik: SMS-001 s/d SMS-072
    // Dengan ini tidak terjadi konflik takeover antar VU untuk apotek yang sama
    const pharmacyNumber = ((__VU - 1) % 72) + 1;
    const customerCode = `SMS-${String(pharmacyNumber).padStart(3, '0')}`;

    const baseHeaders = {
        'User-Agent': `Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 K6GatheringTest/${customerCode}`,
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,application/json,*/*;q=0.8',
        'Accept-Language': 'id-ID,id;q=0.9,en-US;q=0.8',
    };

    let inertiaVersion = '';

    // ------------------------------------------------------------------------
    // TAHAP 1: Buka Halaman Login & Ambil Session + CSRF Cookie (Hanya sekali di awal sesi VU)
    // ------------------------------------------------------------------------
    group('1. Onboarding & Login Apotek', function () {
        const loginPageRes = http.get(`${BASE_URL}/`, {
            headers: baseHeaders,
            tags: { name: 'GET / (Onboarding)' },
        });

        inspectHostingHealth(loginPageRes, 'GET /');
        check(loginPageRes, {
            'halaman login terbuka (200)': (r) => r.status === 200,
        });

        inertiaVersion = extractInertiaVersion(loginPageRes.body);

        // Jeda mengetik kode apotek (2 - 4 detik)
        humanSleep(2, 4);

        const xsrf = getXsrfToken();
        const loginRes = http.post(
            `${BASE_URL}/login-guest`,
            JSON.stringify({
                customer_code: customerCode,
                force_takeover: true, // Pastikan sesi masuk meskipun sebelumnya sempat nyangkut
            }),
            {
                headers: Object.assign({}, baseHeaders, {
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': xsrf,
                }),
                redirects: 5, // Otomatis ikuti redirect ke /katalog
                tags: { name: 'POST /login-guest' },
            }
        );

        inspectHostingHealth(loginRes, 'POST /login-guest');
        catalogDuration.add(loginRes.timings.duration);

        check(loginRes, {
            'login berhasil & masuk katalog (200)': (r) => r.status === 200,
        });

        if (!inertiaVersion) {
            inertiaVersion = extractInertiaVersion(loginRes.body);
        }
    });

    // Setelah login, apotek berada di dalam aplikasi melakukan siklus aktivitas:
    // Auto-polling katalog (tiap ~8 detik), filter pabrik, simulasi hitung reward keranjang, & cek leaderboard
    const loopsInSession = 3;
    for (let i = 0; i < loopsInSession; i++) {
        // Jeda 6.5 - 9.5 detik (mensimulasikan usePoll(8000) di Catalog.jsx + waktu baca user)
        humanSleep(6.5, 9.5);

        // --------------------------------------------------------------------
        // TAHAP 2: Auto-Poll atau Filter Katalog Pabrik (Partial Reload Inertia)
        // --------------------------------------------------------------------
        group('2. Katalog & Auto-Poll (8s Interval)', function () {
            const randomSlug = MANUFACTURER_SLUGS[Math.floor(Math.random() * MANUFACTURER_SLUGS.length)];
            const pollHeaders = Object.assign({}, baseHeaders, {
                'Accept': 'text/html, application/xhtml+xml',
            });

            // Jika kita punya versi Inertia, gunakan header X-Inertia agar respons berupa JSON ringan (hemat bandwidth)
            if (inertiaVersion) {
                pollHeaders['X-Inertia'] = 'true';
                pollHeaders['X-Inertia-Version'] = inertiaVersion;
                pollHeaders['X-Inertia-Partial-Component'] = 'Guest/Catalog';
                pollHeaders['X-Inertia-Partial-Data'] = 'manufacturers,products,event,milestone,currentRewards,ordersCount';
            }

            const catRes = http.get(`${BASE_URL}/katalog?manufacturer=${randomSlug}`, {
                headers: pollHeaders,
                tags: { name: 'GET /katalog (Poll/Filter)' },
            });

            inspectHostingHealth(catRes, 'GET /katalog');
            catalogDuration.add(catRes.timings.duration);

            check(catRes, {
                'katalog sukses dimuat (200)': (r) => r.status === 200,
            });
        });

        humanSleep(4, 7);

        // --------------------------------------------------------------------
        // TAHAP 3: Buka Keranjang & Hitung Simulasi Reward (POST /keranjang/preview-rewards)
        // --------------------------------------------------------------------
        group('3. Keranjang & Simulasi Reward', function () {
            const xsrf = getXsrfToken();
            const previewPayload = JSON.stringify({
                items: [
                    {
                        product_id: 1,
                        sku: 'ZNT-001',
                        name: 'ALLERZEN SYR',
                        manufacturer_name: 'PT. ZENITH PHARMA & BUFA',
                        price: 7500,
                        quantity: 10 + (pharmacyNumber % 15),
                        uom: 'FLS',
                    },
                    {
                        product_id: 54,
                        sku: 'GRH-001',
                        name: 'GRAFAMIC',
                        manufacturer_name: 'PT. GRAHA FARMA',
                        price: 36000,
                        quantity: 5 + (pharmacyNumber % 10),
                        uom: 'BOX',
                    },
                ],
            });

            const previewRes = http.post(`${BASE_URL}/keranjang/preview-rewards`, previewPayload, {
                headers: Object.assign({}, baseHeaders, {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': xsrf,
                }),
                tags: { name: 'POST /keranjang/preview-rewards' },
            });

            inspectHostingHealth(previewRes, 'POST /keranjang/preview-rewards');
            previewRewardDuration.add(previewRes.timings.duration);

            check(previewRes, {
                'simulasi reward berhasil dihitung (200)': (r) => r.status === 200,
            });
        });

        humanSleep(5, 8);

        // --------------------------------------------------------------------
        // TAHAP 4: Cek Klasemen / Leaderboard Apotek (GET /leaderboard)
        // Endpoint ini cukup berat karena menghitung ranking seluruh apotek & pabrik
        // --------------------------------------------------------------------
        group('4. Cek Leaderboard Tamu', function () {
            const lbHeaders = Object.assign({}, baseHeaders);
            if (inertiaVersion) {
                lbHeaders['X-Inertia'] = 'true';
                lbHeaders['X-Inertia-Version'] = inertiaVersion;
            }

            const lbRes = http.get(`${BASE_URL}/leaderboard`, {
                headers: lbHeaders,
                tags: { name: 'GET /leaderboard' },
            });

            inspectHostingHealth(lbRes, 'GET /leaderboard');
            leaderboardDuration.add(lbRes.timings.duration);

            check(lbRes, {
                'leaderboard tamu berhasil dimuat (200)': (r) => r.status === 200,
            });
        });

        // --------------------------------------------------------------------
        // TAHAP 5 (OPSIONAL): Checkout PO Sungguhan
        // Hanya dijalankan jika ENABLE_CHECKOUT=true dan pada loop terakhir
        // --------------------------------------------------------------------
        if (ENABLE_CHECKOUT && i === loopsInSession - 1) {
            humanSleep(3, 5);
            group('5. Submit Checkout PO (Opsional)', function () {
                const xsrf = getXsrfToken();
                const checkoutPayload = JSON.stringify({
                    items: [
                        { product_id: 1, quantity: 2 },
                    ],
                    notes: '[K6-LOAD-TEST] Simulasi Order Otomatis - Harap Hapus',
                    reward_selections: {},
                });

                const coRes = http.post(`${BASE_URL}/checkout`, checkoutPayload, {
                    headers: Object.assign({}, baseHeaders, {
                        'Content-Type': 'application/json',
                        'X-XSRF-TOKEN': xsrf,
                    }),
                    tags: { name: 'POST /checkout' },
                });

                inspectHostingHealth(coRes, 'POST /checkout');
                check(coRes, {
                    'checkout PO berhasil (200/302)': (r) => r.status === 200 || r.status === 302,
                });
            });
        }
    }
}

// ============================================================================
// 5. SKENARIO UTAMA 2: LAYAR PROYEKTOR PANGGUNG (POLLING TIAP 5 DETIK)
// ============================================================================
export function stageProjectorFlow() {
    const res = http.get(`${BASE_URL}/api/stage/data`, {
        headers: {
            'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) StageProjectorDisplay/1.0',
            'Accept': 'application/json',
        },
        tags: { name: 'GET /api/stage/data (Stage Polling 5s)' },
    });

    inspectHostingHealth(res, 'GET /api/stage/data');
    stageApiDuration.add(res.timings.duration);

    check(res, {
        'API stage panggung merespons JSON 200': (r) => r.status === 200,
    });

    // Sesuai Stage/Leaderboard.jsx baris 199: setInterval(..., 5000)
    sleep(5);
}
