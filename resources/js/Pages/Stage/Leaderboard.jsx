import React, { useState, useEffect, useRef } from 'react';
import { Head } from '@inertiajs/react';
import { formatRupiah, formatCountdown } from '../../utils/format';
import PodiumCard from '../../Components/Stage/PodiumCard';
import {
  Trophy,
  Clock,
  Flame,
  Crown,
  Sparkles,
  TrendingUp,
  Play,
  Pause,
  Layers,
  Sparkle,
} from 'lucide-react';

const SLIDE_DURATION_SECONDS = 15;

export default function StageLeaderboard({
  leaderboard: initialLeaderboard,
  leaderboards_by_manufacturer: initialByManufacturer,
  stats_by_manufacturer: initialStatsByManufacturer,
  manufacturers: initialManufacturers,
  stats: initialStats,
  event: initialEvent,
}) {
  const [leaderboard, setLeaderboard] = useState(initialLeaderboard || []);
  const [byManufacturer, setByManufacturer] = useState(initialByManufacturer || {});
  const [statsByManufacturer, setStatsByManufacturer] = useState(initialStatsByManufacturer || {});
  const [manufacturers, setManufacturers] = useState(initialManufacturers || []);
  const [stats, setStats] = useState(initialStats || { total_gmv: 0, total_orders: 0 });
  const [event, setEvent] = useState(initialEvent || {});
  const [countdown, setCountdown] = useState(() => formatCountdown(initialEvent?.session_end_at));

  // Active Category / Manufacturer Tab State
  const [activeTab, setActiveTab] = useState('all'); // 'all' or manufacturer id
  const [isAutoRotate, setIsAutoRotate] = useState(true);
  const [slideSecondsLeft, setSlideSecondsLeft] = useState(SLIDE_DURATION_SECONDS);

  // Dynamic session resolution (per-manufacturer, multi-manufacturer, or overall)
  const resolveCurrentSessionInfo = () => {
    if (activeTab !== 'all') {
      const curM = (manufacturers || []).find((m) => m.id === activeTab);
      if (!curM || curM.is_ordering_open === false) {
        return {
          label: 'Sesi Ditutup',
          text: '00:00:00',
        };
      }
      if (curM.session_end_at) {
        return {
          label: `Sesi ${curM.name.replace(/^PT\.\s*/i, '')}`,
          text: formatCountdown(curM.session_end_at),
        };
      }
      return {
        label: 'Sesi PO',
        text: 'Buka Penuh',
      };
    }

    // Overall
    const openMs = (manufacturers || []).filter((m) => m.is_ordering_open);
    const timedMs = openMs.filter(
      (m) => m.session_end_at && new Date(m.session_end_at) > new Date()
    );

    if (timedMs.length > 0) {
      const latestM = timedMs.reduce((prev, cur) =>
        new Date(cur.session_end_at) > new Date(prev.session_end_at) ? cur : prev
      );
      return {
        label: timedMs.length === 1 ? `Sesi ${latestM.name.replace(/^PT\.\s*/i, '')}` : 'Sesi PO Berakhir',
        text: formatCountdown(latestM.session_end_at),
      };
    }

    if (openMs.length > 0) {
      return {
        label: 'Sesi PO',
        text: 'Buka Penuh',
      };
    }

    if (event?.session_end_at && new Date(event.session_end_at) > new Date()) {
      return {
        label: 'Sesi PO Berakhir',
        text: formatCountdown(event.session_end_at),
      };
    }

    return {
      label: 'Sesi PO',
      text: '00:00:00',
    };
  };

  const [sessionInfo, setSessionInfo] = useState(resolveCurrentSessionInfo);

  // Tabs list: [Overall, ...Manufacturers]
  const tabs = [
    { id: 'all', name: 'Semua Pabrik (Overall)', isOverall: true },
    ...(manufacturers || []).map((m) => ({
      id: m.id,
      name: m.name,
      slug: m.slug,
      is_ordering_open: m.is_ordering_open,
      session_end_at: m.session_end_at,
      isOverall: false,
    })),
  ];

  // Ref to hold current state for interval callbacks
  const activeTabRef = useRef(activeTab);
  activeTabRef.current = activeTab;
  const isAutoRotateRef = useRef(isAutoRotate);
  isAutoRotateRef.current = isAutoRotate;

  // Countdown timer effect
  useEffect(() => {
    setSessionInfo(resolveCurrentSessionInfo());
    const timer = setInterval(() => {
      setSessionInfo(resolveCurrentSessionInfo());
    }, 1000);
    return () => clearInterval(timer);
  }, [activeTab, manufacturers, event?.session_end_at]);

  // Auto-rotate Carousel effect (15s per category)
  useEffect(() => {
    if (!isAutoRotate) return;

    const interval = setInterval(() => {
      setSlideSecondsLeft((prev) => {
        if (prev <= 1) {
          // Time to switch to next tab!
          const currentIndex = tabs.findIndex((t) => t.id === activeTabRef.current);
          const nextIndex = (currentIndex + 1) % tabs.length;
          setActiveTab(tabs[nextIndex].id);
          return SLIDE_DURATION_SECONDS;
        }
        return prev - 1;
      });
    }, 1000);

    return () => clearInterval(interval);
  }, [isAutoRotate, tabs.length]);

  // Real-time polling every 5 seconds for stage display
  useEffect(() => {
    const poller = setInterval(async () => {
      try {
        const res = await fetch('/api/stage/data');
        if (res.ok) {
          const data = await res.json();
          setLeaderboard(data.leaderboard || []);
          setByManufacturer(data.leaderboards_by_manufacturer || {});
          setStatsByManufacturer(data.stats_by_manufacturer || {});
          setManufacturers(data.manufacturers || []);
          setStats(data.stats || { total_gmv: 0, total_orders: 0 });
          setEvent(data.event || {});
        }
      } catch (e) {
        console.error('Stage data fetch error', e);
      }
    }, 5000);

    return () => clearInterval(poller);
  }, []);

  // Handle manual tab selection by operator
  const handleSelectTab = (tabId) => {
    setActiveTab(tabId);
    setSlideSecondsLeft(SLIDE_DURATION_SECONDS); // reset timer
  };

  // Toggle Auto-Rotate Play / Pause
  const handleToggleAutoRotate = () => {
    setIsAutoRotate((prev) => !prev);
    setSlideSecondsLeft(SLIDE_DURATION_SECONDS);
  };

  // Resolve currently active leaderboard data
  const currentLeaderboard =
    activeTab === 'all' ? leaderboard : byManufacturer[activeTab] || [];

  const currentTabObj = tabs.find((t) => t.id === activeTab) || tabs[0];
  const activeGmv =
    activeTab === 'all'
      ? stats.total_gmv
      : statsByManufacturer[activeTab]?.total_gmv || 0;

  // Podium Positions
  const top1 = currentLeaderboard[0];
  const top2 = currentLeaderboard[1];
  const top3 = currentLeaderboard[2];
  const rank4to10 = currentLeaderboard.slice(3, 10);

  // Progress percentage for 15s carousel timer (0 to 100%)
  const progressPercent = ((SLIDE_DURATION_SECONDS - slideSecondsLeft) / SLIDE_DURATION_SECONDS) * 100;

  return (
    <div
      data-theme="stage-display"
      className="min-h-screen bg-slate-50 text-slate-900 flex flex-col justify-between p-4 lg:p-6 select-none overflow-hidden relative font-sans"
    >
      <Head title="Live Stage Leaderboard - Most Spender" />

      {/* Ambient Radial Lights in Background */}
      <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[900px] h-[350px] bg-brand-500/10 rounded-full blur-[140px] pointer-events-none" />
      <div className="absolute bottom-10 right-10 w-[600px] h-[300px] bg-amber-400/15 rounded-full blur-[120px] pointer-events-none" />

      {/* ========================================================================= */}
      {/* 1. TOP HEADER & METRICS                                                   */}
      {/* ========================================================================= */}
      <header className="relative z-10 flex items-center justify-between border-b border-slate-200/90 pb-3.5">
        <div className="flex items-center gap-3.5">
          <div className="w-12 h-12 lg:w-14 lg:h-14 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-400 flex items-center justify-center text-slate-950 shadow-md shrink-0">
            <Trophy className="w-7 h-7 lg:w-8 lg:h-8 stroke-[2.5]" />
          </div>
          <div>
            <div className="flex items-center gap-2">
              <span className="bg-brand-50 text-brand-700 border border-brand-200 text-[10px] lg:text-xs font-black px-2.5 py-0.5 rounded-full tracking-widest uppercase">
                LIVE GATHERING 2026
              </span>
              <span className="text-xs text-slate-500 font-mono tracking-wider hidden sm:inline">
                SMS MEDIKA SURABAYA
              </span>
            </div>
            <h1 className="text-2xl lg:text-3xl font-black text-brand-900 tracking-tight mt-0.5">
              MOST SPENDER LEADERBOARD
            </h1>
          </div>
        </div>

        {/* Live Metrics: Active GMV & Session Countdown */}
        <div className="flex items-center gap-3.5 lg:gap-5">
          <div className="bg-white border border-slate-200/90 rounded-2xl px-4 lg:px-5 py-2.5 text-right shadow-xs">
            <span className="text-[10px] lg:text-xs text-slate-500 block font-bold uppercase tracking-wider truncate max-w-[200px]">
              {activeTab === 'all' ? 'Total GMV Malam Ini' : `Total PO: ${currentTabObj.name}`}
            </span>
            <span className="text-xl lg:text-2xl font-black text-emerald-600 font-mono">
              {formatRupiah(activeGmv)}
            </span>
          </div>

          <div className="bg-gradient-to-r from-brand-700 via-brand-600 to-brand-800 text-white border border-brand-500/30 rounded-2xl px-5 lg:px-6 py-2.5 text-center shadow-md relative overflow-hidden">
            <div className="absolute top-0 right-0 -mr-4 -mt-4 w-16 h-16 bg-amber-400/20 rounded-full blur-xl pointer-events-none" />
            <span className="text-[10px] lg:text-xs text-brand-100 block font-bold tracking-widest uppercase flex items-center justify-center gap-1.5 relative z-10">
              <Clock className="w-3.5 h-3.5 text-amber-300" />
              <span>{sessionInfo.label}</span>
            </span>
            <span className="text-2xl lg:text-3xl font-black text-amber-300 font-mono tracking-wider relative z-10">
              {sessionInfo.text}
            </span>
          </div>
        </div>
      </header>

      {/* ========================================================================= */}
      {/* 2. MANUFACTURER TABS & AUTO-CYCLE CAROUSEL BAR                            */}
      {/* ========================================================================= */}
      <section className="relative z-10 my-2.5 flex items-center justify-between gap-3 bg-white/80 backdrop-blur-md border border-slate-200/90 p-2 rounded-2xl shadow-xs">
        {/* Horizontal Category Tabs */}
        <div className="flex items-center gap-1.5 lg:gap-2 overflow-x-auto no-scrollbar py-0.5">
          {tabs.map((tab) => {
            const isActive = activeTab === tab.id;

            return (
              <button
                key={tab.id}
                type="button"
                onClick={() => handleSelectTab(tab.id)}
                className={`relative px-3.5 lg:px-4 py-2 rounded-xl text-xs lg:text-sm font-bold whitespace-nowrap transition-all flex items-center gap-1.5 overflow-hidden ${
                  isActive
                    ? 'bg-brand-600 text-white shadow-sm ring-2 ring-brand-600/30'
                    : 'bg-slate-50 text-slate-700 hover:bg-slate-100 border border-slate-200/70'
                }`}
              >
                {tab.isOverall ? (
                  <span>⭐ {tab.name}</span>
                ) : (
                  <span>{tab.name}</span>
                )}

                {/* Animated Timer Progress Bar inside Active Tab when Auto-Rotate is ON */}
                {isActive && isAutoRotate && (
                  <span
                    className="absolute bottom-0 left-0 h-1 bg-amber-400 transition-all duration-1000 ease-linear rounded-full"
                    style={{ width: `${progressPercent}%` }}
                  />
                )}
              </button>
            );
          })}
        </div>

        {/* Auto-Rotate Controls (Operator Switch) */}
        <div className="flex items-center gap-2 shrink-0 border-l border-slate-200 pl-3">
          <button
            type="button"
            onClick={handleToggleAutoRotate}
            title={isAutoRotate ? 'Jeda pergantian layar otomatis' : 'Lanjutkan rotasi otomatis'}
            className={`flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-xs ${
              isAutoRotate
                ? 'bg-emerald-50 text-emerald-700 border border-emerald-300 hover:bg-emerald-100'
                : 'bg-amber-50 text-amber-800 border border-amber-300 hover:bg-amber-100'
            }`}
          >
            {isAutoRotate ? (
              <>
                <Pause className="w-3.5 h-3.5 fill-emerald-600 text-emerald-600" />
                <span className="hidden sm:inline">Auto-Rotate ({slideSecondsLeft}s)</span>
              </>
            ) : (
              <>
                <Play className="w-3.5 h-3.5 fill-amber-600 text-amber-600" />
                <span className="hidden sm:inline">Paused (Klik Play)</span>
              </>
            )}
          </button>
        </div>
      </section>

      {/* ========================================================================= */}
      {/* 3. MAIN AREA: TOP 3 GRAND PODIUM (~60% HEIGHT)                             */}
      {/* ========================================================================= */}
      <section className="relative z-10 flex-1 flex flex-col justify-center my-auto pt-3 pb-1">
        {/* Stage Subtitle - Prominent & Highly Legible from Medium-Far Distance */}
        <div className="text-center mb-4 lg:mb-5">
          <div className="inline-flex items-center gap-2.5 lg:gap-3 bg-white/95 backdrop-blur-md px-6 lg:px-8 py-2 lg:py-2.5 rounded-2xl border-2 border-amber-300/90 shadow-sm">
            <Sparkles className="w-5 h-5 lg:w-6 lg:h-6 text-amber-500 shrink-0" />
            <h2 className="text-base lg:text-2xl font-black text-slate-900 uppercase tracking-wider">
              {activeTab === 'all'
                ? 'PODIUM JUARA UTAMA (SEMUA PABRIKAN)'
                : `PODIUM JUARA: ${currentTabObj.name.toUpperCase()}`}
            </h2>
            <Sparkles className="w-5 h-5 lg:w-6 lg:h-6 text-amber-500 shrink-0" />
          </div>
        </div>

        {/* 3-Column Centered Podium with Equal Widths & Relative Sizing */}
        <div className="max-w-6xl mx-auto w-full grid grid-cols-3 gap-3 md:gap-5 lg:gap-8 items-end px-2">
          {/* -------------------- PODIUM #2 (SILVER - KIRI) -------------------- */}
          <PodiumCard
            rank={2}
            item={top2}
            categoryName={currentTabObj.name}
          />

          {/* -------------------- PODIUM #1 (GOLD - TENGAH ELEVATED) -------------------- */}
          <PodiumCard
            rank={1}
            item={top1}
            categoryName={currentTabObj.name}
          />

          {/* -------------------- PODIUM #3 (BRONZE - KANAN) -------------------- */}
          <PodiumCard
            rank={3}
            item={top3}
            categoryName={currentTabObj.name}
          />
        </div>
      </section>

      {/* ========================================================================= */}
      {/* 4. LOWER AREA: PERINGKAT 4 — 10 HORIZONTAL GRID (~35% HEIGHT)            */}
      {/* ========================================================================= */}
      <section className="relative z-10 bg-white border border-slate-200/90 rounded-3xl p-3.5 lg:p-4 shadow-sm mb-2.5">
        <div className="flex items-center justify-between mb-2.5 px-1 border-b border-slate-100 pb-2">
          <div className="flex items-center gap-2">
            <TrendingUp className="w-4 h-4 text-emerald-600" />
            <span className="text-xs font-bold uppercase tracking-wider text-slate-800">
              PERINGKAT 4 SAMPAI 10 &bull; {currentTabObj.name.toUpperCase()}
            </span>
          </div>
          <span className="text-[10px] text-brand-700 bg-brand-50 border border-brand-200 px-2.5 py-0.5 rounded-full font-semibold">
            Live Leaderboard
          </span>
        </div>

        {/* 7 Columns Horizontal Row */}
        {rank4to10.length === 0 ? (
          <p className="text-xs text-slate-400 text-center py-4">
            Belum ada data transaksi di peringkat 4–10 untuk kategori ini.
          </p>
        ) : (
          <div className="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2 lg:gap-2.5">
            {rank4to10.map((item) => (
              <div
                key={item.id}
                className="bg-slate-50/80 hover:bg-brand-50/50 border border-slate-200/80 hover:border-brand-300 rounded-2xl p-2.5 flex flex-col justify-between transition-all shadow-2xs"
              >
                <div className="flex items-center justify-between gap-1 mb-1">
                  <span className="w-6 h-6 rounded-lg bg-white text-slate-700 font-black text-xs flex items-center justify-center shrink-0 border border-slate-200 shadow-2xs">
                    #{item.rank}
                  </span>
                  <span className="text-[10px] text-slate-400 truncate">
                    {item.city || 'Jawa Tengah'}
                  </span>
                </div>

                <h4 className="text-xs font-bold text-slate-900 truncate my-0.5" title={item.name}>
                  {item.name}
                </h4>

                <div className="pt-1.5 border-t border-slate-200/60 mt-1">
                  <span className="text-xs font-black text-brand-700 font-mono block truncate">
                    {formatRupiah(item.total_spending)}
                  </span>
                </div>
              </div>
            ))}
          </div>
        )}

        {/* Tie-breaker footnote */}
        <div className="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400">
          <span>* Peringkat ditentukan akumulasi transaksi valid; jika nominal sama, ditentukan waktu pesanan lebih awal.</span>
          <span>PT. Satria Medika Sejahtera</span>
        </div>
      </section>

      {/* ========================================================================= */}
      {/* 5. BOTTOM RUNNING MARQUEE TICKER                                          */}
      {/* ========================================================================= */}
      <footer className="relative z-10 bg-white border border-slate-200/90 rounded-2xl py-2 px-4 shadow-sm flex items-center gap-3 overflow-hidden">
        <div className="flex items-center gap-1.5 shrink-0 bg-gradient-to-r from-amber-500 to-amber-400 text-slate-950 px-2.5 py-1 rounded-xl text-[11px] font-black uppercase tracking-wider shadow-2xs">
          <Flame className="w-3.5 h-3.5 fill-slate-950" />
          <span>PENGUMUMAN</span>
        </div>
        <div className="overflow-hidden whitespace-nowrap flex-1">
          <p className="text-xs font-semibold text-slate-700 inline-block animate-marquee tracking-wide">
            {event?.running_ticker_text ||
              '🔥 Flash Deal Sesi PO Gathering SMS Sedang Berlangsung! Kumpulkan Order Sebanyak-banyaknya untuk Memenangkan Grand Prize Most Spender Malam Ini!'}
          </p>
        </div>
      </footer>
    </div>
  );
}
