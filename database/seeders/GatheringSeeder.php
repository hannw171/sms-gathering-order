<?php

namespace Database\Seeders;

use App\Models\EventSetting;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\RewardTier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GatheringSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default Admin User
        User::firstOrCreate(
            ['email' => 'admin@sms.com'],
            [
                'name' => 'Admin PBF Gathering',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Event Settings
        EventSetting::updateOrCreate(
            ['id' => 1],
            [
                'event_name' => 'SMS Gathering & Order Night 2026',
                'session_end_at' => now()->addMinutes(45),
                'is_ordering_active' => true,
                'is_leaderboard_visible' => true,
                'doorprize_announcement' => 'DOORPRIZE EXCLUSIVE: Grand Prize Emas Logam Mulia & Paket Liburan',
                'running_ticker_text' => '🔥 Flash Deal Sesi PO Gathering SMS Sedang Berlangsung! Kumpulkan Order Sebanyak-banyaknya untuk Memenangkan Grand Prize Most Spender Malam Ini!',
            ]
        );

        // 3. Reward Tiers
        $tiers = [
            [
                'tier_level' => 1,
                'name' => 'Tier 1 - Logam Mulia 0.5g',
                'min_spending' => 5000000,
                'reward_description' => 'Logam Mulia 0.5 Gram',
                'badge_icon' => '🥉',
            ],
            [
                'tier_level' => 2,
                'name' => 'Tier 2 - Logam Mulia 1g',
                'min_spending' => 15000000,
                'reward_description' => 'Logam Mulia 1 Gram',
                'badge_icon' => '🥈',
            ],
            [
                'tier_level' => 3,
                'name' => 'Tier 3 - Smart TV 43 Inch',
                'min_spending' => 30000000,
                'reward_description' => 'Smart TV 43 Inch',
                'badge_icon' => '🥇',
            ],
            [
                'tier_level' => 4,
                'name' => 'Tier 4 - Motor Listrik & Emas',
                'min_spending' => 60000000,
                'reward_description' => 'Motor Listrik & Emas 5 Gram',
                'badge_icon' => '👑',
            ],
        ];

        foreach ($tiers as $tier) {
            RewardTier::updateOrCreate(['tier_level' => $tier['tier_level']], $tier);
        }

        // 4. Manufacturers
        $manufacturers = [
            ['name' => 'Kalbe Farma', 'slug' => 'kalbe-farma', 'sort_order' => 1],
            ['name' => 'Sanbe Farma', 'slug' => 'sanbe-farma', 'sort_order' => 2],
            ['name' => 'Dexa Medica', 'slug' => 'dexa-medica', 'sort_order' => 3],
            ['name' => 'Novell Pharma', 'slug' => 'novell-pharma', 'sort_order' => 4],
            ['name' => 'Kimia Farma', 'slug' => 'kimia-farma', 'sort_order' => 5],
        ];

        $mMap = [];
        foreach ($manufacturers as $m) {
            $created = Manufacturer::updateOrCreate(['slug' => $m['slug']], $m);
            $mMap[$m['slug']] = $created;
        }

        // 5. Products
        $products = [
            // Kalbe
            [
                'manufacturer_id' => $mMap['kalbe-farma']->id,
                'name' => 'Promag Tablet Kunyah',
                'sku' => 'KLB-001',
                'uom' => 'Box @ 30 Blister',
                'price' => 245000,
                'description' => 'Obat maag antasida magnesium hidroksida & hidrotalsit.',
            ],
            [
                'manufacturer_id' => $mMap['kalbe-farma']->id,
                'name' => 'Woods Peppermint Antitussive 100ml',
                'sku' => 'KLB-002',
                'uom' => 'Box @ 12 Botol',
                'price' => 395000,
                'description' => 'Sirup batuk kering dengan ekstrak peppermint alami.',
            ],
            [
                'manufacturer_id' => $mMap['kalbe-farma']->id,
                'name' => 'Cerebrofort Gold Sirup Jeruk 100ml',
                'sku' => 'KLB-003',
                'uom' => 'Box @ 24 Botol',
                'price' => 580000,
                'description' => 'Multivitamin penambah nafsu makan dan daya ingat anak.',
            ],
            [
                'manufacturer_id' => $mMap['kalbe-farma']->id,
                'name' => 'Entrostop Herbal Anak',
                'sku' => 'KLB-004',
                'uom' => 'Box @ 10 Sachet',
                'price' => 145000,
                'description' => 'Obat diare alami dari daun jambu biji & kunyit.',
            ],
            // Sanbe
            [
                'manufacturer_id' => $mMap['sanbe-farma']->id,
                'name' => 'Sanmol Forte Sirup 60ml',
                'sku' => 'SNB-001',
                'uom' => 'Box @ 20 Botol',
                'price' => 470000,
                'description' => 'Paracetamol 250mg/5ml penurun demam & pereda nyeri.',
            ],
            [
                'manufacturer_id' => $mMap['sanbe-farma']->id,
                'name' => 'Amoxicillin 500mg Kaplet',
                'sku' => 'SNB-002',
                'uom' => 'Box @ 100 Kaplet',
                'price' => 185000,
                'description' => 'Antibiotik spektrum luas amoxicillin trihydrate.',
            ],
            [
                'manufacturer_id' => $mMap['sanbe-farma']->id,
                'name' => 'Flucadex Sirup 60ml',
                'sku' => 'SNB-003',
                'uom' => 'Box @ 15 Botol',
                'price' => 310000,
                'description' => 'Obat flu, batuk, dan hidung tersumbat.',
            ],
            // Dexa
            [
                'manufacturer_id' => $mMap['dexa-medica']->id,
                'name' => 'Rhinos SR Kapsul Lepas Lambat',
                'sku' => 'DXA-001',
                'uom' => 'Box @ 50 Kapsul',
                'price' => 465000,
                'description' => 'Loratadine & pseudoephedrine untuk rhinitis alergi.',
            ],
            [
                'manufacturer_id' => $mMap['dexa-medica']->id,
                'name' => 'Stimuno Forte Kapsul',
                'sku' => 'DXA-002',
                'uom' => 'Box @ 30 Kapsul',
                'price' => 290000,
                'description' => 'Imunomodulator ekstrak meniran untuk daya tahan tubuh.',
            ],
            [
                'manufacturer_id' => $mMap['dexa-medica']->id,
                'name' => 'Voltadex 50mg Tablet',
                'sku' => 'DXA-003',
                'uom' => 'Box @ 50 Tablet',
                'price' => 175000,
                'description' => 'Diclofenac sodium obat anti-inflamasi non-steroid.',
            ],
            // Novell
            [
                'manufacturer_id' => $mMap['novell-pharma']->id,
                'name' => 'Cravit Ophthalmic Drops 5ml',
                'sku' => 'NVL-001',
                'uom' => 'Botol 5ml',
                'price' => 125000,
                'description' => 'Levofloxacin tetes mata infeksi bakteri.',
            ],
            [
                'manufacturer_id' => $mMap['novell-pharma']->id,
                'name' => 'Epexol Sirup 120ml',
                'sku' => 'NVL-002',
                'uom' => 'Box @ 10 Botol',
                'price' => 260000,
                'description' => 'Ambroxol HCl mukolitik pengencer dahak.',
            ],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(['sku' => $p['sku']], $p);
        }

        // 6. Pharmacies (Tamu Undangan)
        $pharmacies = [
            [
                'customer_code' => 'APT-001',
                'name' => 'Apotek Kimia Mandiri',
                'table_number' => 'Meja 08',
                'owner_name' => 'Ibu Ratna Kumalasari',
                'phone' => '081234567890',
                'city' => 'Surabaya',
                'sales_pic' => 'Ahmad Fauzi',
                'is_checked_in' => true,
                'checked_in_at' => now(),
            ],
            [
                'customer_code' => 'APT-002',
                'name' => 'Apotek Sehat Bahagia',
                'table_number' => 'Meja 02',
                'owner_name' => 'Bpk. Bambang Wijaya',
                'phone' => '081298765432',
                'city' => 'Sidoarjo',
                'sales_pic' => 'Ahmad Fauzi',
                'is_checked_in' => true,
                'checked_in_at' => now(),
            ],
            [
                'customer_code' => 'APT-003',
                'name' => 'Apotek Berkah Medika',
                'table_number' => 'Meja 05',
                'owner_name' => 'Ibu Dewi Sartika',
                'phone' => '081345678901',
                'city' => 'Malang',
                'sales_pic' => 'Dwi Handayani',
                'is_checked_in' => true,
                'checked_in_at' => now(),
            ],
            [
                'customer_code' => 'APT-004',
                'name' => 'Apotek Mitra Husada',
                'table_number' => 'Meja 12',
                'owner_name' => 'Bpk. Hendra Gunawan',
                'phone' => '081387654321',
                'city' => 'Gresik',
                'sales_pic' => 'Dwi Handayani',
                'is_checked_in' => true,
                'checked_in_at' => now(),
            ],
            [
                'customer_code' => 'APT-005',
                'name' => 'Apotek Sumber Waras Utama',
                'table_number' => 'Meja 04',
                'owner_name' => 'Ibu Lilik Suryani',
                'phone' => '081567890123',
                'city' => 'Mojokerto',
                'sales_pic' => 'Ahmad Fauzi',
                'is_checked_in' => true,
                'checked_in_at' => now(),
            ],
            [
                'customer_code' => 'APT-006',
                'name' => 'Apotek K-24 Raya Darmo',
                'table_number' => 'Meja 07',
                'owner_name' => 'Bpk. Agung Prasetyo',
                'phone' => '081678901234',
                'city' => 'Surabaya',
                'sales_pic' => 'Dwi Handayani',
                'is_checked_in' => true,
                'checked_in_at' => now(),
            ],
            [
                'customer_code' => 'APT-007',
                'name' => 'Apotek Viva Generik Rungkut',
                'table_number' => 'Meja 09',
                'owner_name' => 'Ibu Siska Amelia',
                'phone' => '081789012345',
                'city' => 'Surabaya',
                'sales_pic' => 'Ahmad Fauzi',
                'is_checked_in' => true,
                'checked_in_at' => now(),
            ],
        ];

        $pMap = [];
        foreach ($pharmacies as $p) {
            $created = Pharmacy::updateOrCreate(['customer_code' => $p['customer_code']], $p);
            $pMap[$p['customer_code']] = $created;
        }

        // 7. Initial Seed Orders for Leaderboard Simulation
        $seedOrders = [
            [
                'code' => 'APT-002', // Juara 1
                'amount' => 42500000,
                'items' => 55,
                'tier' => 3,
            ],
            [
                'code' => 'APT-006', // Juara 2
                'amount' => 31200000,
                'items' => 40,
                'tier' => 3,
            ],
            [
                'code' => 'APT-003', // Juara 3
                'amount' => 19800000,
                'items' => 28,
                'tier' => 2,
            ],
            [
                'code' => 'APT-001', // Apotek Kimia Mandiri (Tamu aktif saat ini: Rank #4 / #8)
                'amount' => 9750000,
                'items' => 16,
                'tier' => 1,
            ],
            [
                'code' => 'APT-005',
                'amount' => 6400000,
                'items' => 10,
                'tier' => 1,
            ],
        ];

        foreach ($seedOrders as $idx => $so) {
            $pharmacy = $pMap[$so['code']];
            Order::firstOrCreate(
                ['order_number' => 'PO-INIT-00' . ($idx + 1)],
                [
                    'pharmacy_id' => $pharmacy->id,
                    'total_amount' => $so['amount'],
                    'total_items' => $so['items'],
                    'unlocked_reward_tier_id' => $so['tier'],
                    'status' => 'confirmed',
                    'notes' => 'Simulasi Order Gathering Awal',
                ]
            );
        }
    }
}
