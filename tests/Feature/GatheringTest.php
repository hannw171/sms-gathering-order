<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageEventSettings;
use App\Filament\Pages\RewardRecapReport;
use App\Models\EventSetting;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\RewardTier;
use App\Models\User;
use App\Services\GatheringService;
use App\Services\RewardCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GatheringTest extends TestCase
{
    use RefreshDatabase;

    protected Pharmacy $pharmacy;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        EventSetting::current();

        $manufacturer = Manufacturer::create([
            'name' => 'Kalbe Farma',
            'slug' => 'kalbe-farma',
            'is_ordering_active' => true,
        ]);

        $this->product = Product::create([
            'manufacturer_id' => $manufacturer->id,
            'name' => 'Promag Tablet',
            'sku' => 'KLB-001',
            'uom' => 'Box',
            'price' => 200000,
        ]);

        $this->pharmacy = Pharmacy::create([
            'name' => 'Apotek Kimia Mandiri',
            'customer_code' => 'APT-001',
        ]);

        RewardTier::create([
            'tier_level' => 1,
            'name' => 'Tier 1 - Logam Mulia',
            'min_spending' => 500000,
            'reward_description' => 'Emas 0.5g',
        ]);
    }

    public function test_guest_can_view_login_page(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_guest_can_login_with_valid_code(): void
    {
        $response = $this->post('/login-guest', [
            'customer_code' => 'APT-001',
        ]);

        $response->assertRedirect('/katalog');
        $this->assertEquals($this->pharmacy->id, session('guest_pharmacy_id'));
    }

    public function test_guest_can_view_cart_page(): void
    {
        $response = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->get('/keranjang');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Guest/Cart'));
    }

    public function test_guest_can_view_catalog_page(): void
    {
        $response = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->get('/katalog');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Guest/Catalog'));
    }

    public function test_catalog_orders_manufacturers_with_open_sessions_first(): void
    {
        // First manufacturer is closed
        $this->product->manufacturer->update([
            'is_ordering_active' => false,
            'sort_order' => 1,
        ]);

        // Second manufacturer is open
        $openM = Manufacturer::create([
            'name' => 'Open Pharma',
            'slug' => 'open-pharma',
            'is_active' => true,
            'is_ordering_active' => true,
            'sort_order' => 2,
        ]);

        $response = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->get('/katalog');

        $response->assertStatus(200);
        $response->assertInertia(function ($page) use ($openM) {
            $page->component('Guest/Catalog');
            $manufacturers = $page->toArray()['props']['manufacturers'];
            $this->assertEquals($openM->id, $manufacturers[0]['id']);
        });
    }

    public function test_guest_can_view_leaderboard_page(): void
    {
        $response = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->get('/leaderboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Guest/Leaderboard'));
    }

    public function test_guest_can_view_orders_page(): void
    {
        $response = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->get('/pesanan');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Guest/Orders'));
    }

    public function test_anyone_can_view_stage_page(): void
    {
        $response = $this->get('/stage');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Stage/Leaderboard'));
    }

    public function test_device_conflict_triggered_when_active_on_another_device(): void
    {
        $this->pharmacy->update([
            'device_session_token' => 'another-active-session-id',
            'device_name' => 'Chrome Mobile • Samsung S23',
            'last_active_at' => now()->subMinutes(5),
        ]);

        $response = $this->post('/login-guest', [
            'customer_code' => 'APT-001',
        ]);

        $response->assertSessionHas('conflict', true);
        $response->assertSessionHas('conflictData');
        $conflictData = session('conflictData');
        $this->assertNotEmpty($conflictData['last_active_time']);
        $this->assertNull(session('guest_pharmacy_id'));
    }

    public function test_guest_can_force_takeover_conflicted_session(): void
    {
        $this->pharmacy->update([
            'device_session_token' => 'another-active-session-id',
        ]);

        $response = $this->post('/login-guest', [
            'customer_code' => 'APT-001',
            'force_takeover' => true,
        ]);

        $response->assertRedirect('/katalog');
        $this->assertEquals($this->pharmacy->id, session('guest_pharmacy_id'));
    }

    public function test_guest_login_returns_not_found_on_invalid_code(): void
    {
        $response = $this->post('/login-guest', [
            'customer_code' => 'KODE-PALSU-999',
        ]);

        $response->assertSessionHas('notFound', true);
        $response->assertSessionHasErrors('customer_code');
    }

    public function test_guest_can_view_catalog_when_logged_in(): void
    {
        $response = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->get('/katalog');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Guest/Catalog')
            ->has('currentRewards')
            ->has('ordersCount')
            ->has('milestone')
        );
    }

    public function test_guest_can_checkout_order(): void
    {
        $response = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->post('/checkout', [
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 3,
                    ],
                ],
                'notes' => 'Tolong dibungkus rapi',
            ]);

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals(600000, $order->total_amount);
        $this->assertEquals(3, $order->total_items);
        $response->assertRedirect('/order/sukses/'.$order->id);
    }

    public function test_guest_can_preview_rewards_in_cart(): void
    {
        $response = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->postJson('/keranjang/preview-rewards', [
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 2,
                        'price' => 200000,
                    ],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'projected',
                'unlocked_now',
            ]);
    }

    public function test_guest_can_checkout_with_reward_selections_and_view_success(): void
    {
        $response = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->post('/checkout', [
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 5,
                    ],
                ],
                'notes' => 'Pesanan dengan pilihan hadiah',
                'reward_selections' => [
                    [
                        'manufacturer_slug' => 'interbat',
                        'selected_option' => 'Elektrik Kettle',
                    ],
                ],
            ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertIsArray($order->reward_selections);
        $this->assertEquals('Elektrik Kettle', $order->reward_selections[0]['selected_option']);

        $successResponse = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->get('/order/sukses/'.$order->id);

        $successResponse->assertStatus(200);
    }

    public function test_guest_can_checkout_with_multiple_manufacturer_reward_choices(): void
    {
        $response = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->post('/checkout', [
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 2,
                    ],
                ],
                'notes' => 'Pesanan dengan multiple hadiah pilihan',
                'reward_selections' => [
                    'graha_neo' => 'Magic Com Cosmos',
                    'exeltis_lanso' => 'Wajan Hitam 24cm (Tier 25 Box)',
                ],
            ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertIsArray($order->reward_selections);
        $this->assertEquals('Magic Com Cosmos', $order->reward_selections['graha_neo']);
        $this->assertEquals('Wajan Hitam 24cm (Tier 25 Box)', $order->reward_selections['exeltis_lanso']);

        $response->assertRedirect('/order/sukses/'.$order->id);
    }

    public function test_stage_leaderboard_page_and_api(): void
    {
        $response = $this->get('/stage');
        $response->assertStatus(200);

        $apiResponse = $this->getJson('/api/stage/data');
        $apiResponse->assertStatus(200)
            ->assertJsonStructure([
                'leaderboard',
                'leaderboards_by_manufacturer',
                'stats_by_manufacturer',
                'manufacturers',
                'stats' => ['total_gmv', 'total_orders'],
                'event',
            ]);
    }

    public function test_end_to_end_gathering_order_and_reporting_lifecycle(): void
    {
        // 1. Guest Log in
        $loginRes = $this->post('/login-guest', [
            'customer_code' => $this->pharmacy->customer_code,
        ]);
        $loginRes->assertRedirect('/katalog');
        $this->assertEquals($this->pharmacy->id, session('guest_pharmacy_id'));

        // 2. Checkout order with products
        $checkoutRes = $this->post('/checkout', [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                ],
            ],
            'notes' => 'Pesanan E2E Acara',
            'reward_selections' => [
                'interbat' => 'Panci Kuah',
            ],
        ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals(2000000, $order->total_amount);
        $checkoutRes->assertRedirect('/order/sukses/'.$order->id);

        // 3. Stage API updates
        $apiRes = $this->getJson('/api/stage/data');
        $apiRes->assertStatus(200);
        $stageLeaderboard = collect($apiRes->json('leaderboard'));
        $topPharmacy = $stageLeaderboard->first();
        $this->assertEquals($this->pharmacy->name, $topPharmacy['name']);
        $this->assertEquals(2000000, $topPharmacy['total_spending']);

        // 4. Admin Handover flow
        $admin = User::factory()->create();
        $this->actingAs($admin);

        Livewire::test(RewardRecapReport::class)
            ->callTableAction('handover', $this->pharmacy, data: [
                'receiver_name' => 'Apt. Pengelola',
                'notes' => 'Diserahkan langsung',
            ]);

        $this->pharmacy->refresh();
        $this->assertNotNull($this->pharmacy->reward_handed_over_at);
        $this->assertEquals('Apt. Pengelola', $this->pharmacy->reward_receiver_name);
    }

    public function test_logged_in_guest_visiting_root_redirects_to_catalog(): void
    {
        $response = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->get('/');

        $response->assertRedirect('/katalog');
    }

    public function test_previous_device_is_terminated_after_another_device_takes_over(): void
    {
        // 1. Device A was logged in with token A, then Device B takes over and sets token B
        $tokenDeviceA = 'token-device-a';
        $tokenDeviceB = 'token-device-b';

        $this->pharmacy->update([
            'device_session_token' => $tokenDeviceB,
        ]);

        // 2. Device A (having token A in session) attempts to visit catalog
        $response = $this->withSession([
            'guest_pharmacy_id' => $this->pharmacy->id,
            'guest_device_token' => $tokenDeviceA,
        ])->get('/katalog');

        $response->assertRedirect('/');
        $response->assertSessionHas('sessionTerminated', true);
        $this->assertNull(session('guest_pharmacy_id'));

        // 3. Device A attempts to checkout
        $checkoutResponse = $this->withSession([
            'guest_pharmacy_id' => $this->pharmacy->id,
            'guest_device_token' => $tokenDeviceA,
        ])->post('/checkout', [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $checkoutResponse->assertRedirect('/');
        $this->assertEquals(0, Order::count());
    }

    public function test_guest_can_view_order_history_and_reward_breakdown_page(): void
    {
        $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->post('/checkout', [
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 5,
                    ],
                ],
                'notes' => 'Pesanan Riwayat Test',
            ]);

        $response = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->get('/pesanan');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Guest/Orders')
            ->has('pharmacy')
            ->has('milestone')
            ->has('rewards')
            ->has('orders', 1)
        );
    }

    public function test_guest_can_view_leaderboard_with_manufacturer_standings(): void
    {
        $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->post('/checkout', [
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 2,
                    ],
                ],
            ]);

        $response = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->get('/leaderboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Guest/Leaderboard')
            ->has('leaderboard')
            ->has('leaderboards_by_manufacturer')
            ->has('manufacturers')
            ->has('myStandings')
            ->has('myStandings.overall')
            ->has('myStandings.manufacturers')
            ->where('myStandings.overall.rank', 1)
        );
    }

    public function test_admin_can_access_and_save_event_settings_page(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin)
            ->get('/admin/manage-event-settings')
            ->assertSuccessful();

        Livewire::actingAs($admin)
            ->test(ManageEventSettings::class)
            ->set('data.venue_name', 'Grand Ballroom Hotel Aston')
            ->set('data.registration_schedule', '09:00 - 12:00 WIB')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('Grand Ballroom Hotel Aston', EventSetting::current()->venue_name);
        $this->assertEquals('09:00 - 12:00 WIB', EventSetting::current()->registration_schedule);
    }

    public function test_admin_can_trigger_event_setting_header_actions(): void
    {
        $admin = User::factory()->create();

        // 1. Open all manufacturers
        Livewire::actingAs($admin)
            ->test(ManageEventSettings::class)
            ->callAction('open_all_manufacturers');

        $this->assertEquals(0, Manufacturer::where('is_ordering_active', false)->count());

        // 2. Close all manufacturers
        Livewire::actingAs($admin)
            ->test(ManageEventSettings::class)
            ->callAction('close_all_manufacturers');

        $this->assertEquals(0, Manufacturer::where('is_ordering_active', true)->count());

        // 3. Set manufacturer session with duration
        $manufacturer = $this->product->manufacturer;
        Livewire::actingAs($admin)
            ->test(ManageEventSettings::class)
            ->callAction('set_manufacturer_session', [
                'manufacturer_ids' => [$manufacturer->id],
                'duration_minutes' => '30',
            ]);

        $manufacturer->refresh();
        $this->assertTrue($manufacturer->is_ordering_active);
        $this->assertNotNull($manufacturer->session_end_at);
    }

    public function test_checkout_is_blocked_when_session_po_is_not_open(): void
    {
        // Scheduled in future
        EventSetting::current()->update([
            'is_ordering_active' => true,
            'session_start_at' => now()->addHour(),
            'session_end_at' => now()->addHours(3),
        ]);

        $response = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->post('/checkout', [
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 1,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('error');
        $this->assertEquals(0, Order::count());
    }

    public function test_checkout_is_blocked_when_product_manufacturer_session_is_closed(): void
    {
        // General event session is open
        EventSetting::current()->update([
            'is_ordering_active' => true,
            'session_start_at' => now()->subHour(),
            'session_end_at' => now()->addHour(),
        ]);

        // Manufacturer session is closed
        $this->product->manufacturer->update([
            'is_ordering_active' => false,
        ]);

        $response = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->post('/checkout', [
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 1,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('error');
        $this->assertEquals(0, Order::count());
    }

    public function test_checkout_is_blocked_when_product_manufacturer_session_expired(): void
    {
        // General event session is open
        EventSetting::current()->update([
            'is_ordering_active' => true,
            'session_start_at' => now()->subHour(),
            'session_end_at' => now()->addHour(),
        ]);

        // Manufacturer session ended in the past
        $this->product->manufacturer->update([
            'is_ordering_active' => true,
            'session_end_at' => now()->subMinutes(5),
        ]);

        $response = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->post('/checkout', [
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 1,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('error');
        $this->assertEquals(0, Order::count());
    }

    public function test_admin_can_manage_manufacturer_sessions_in_event_settings(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin_test_mfg@example.com',
        ]);

        $mfg = $this->product->manufacturer;
        $mfg->update(['is_ordering_active' => false, 'session_end_at' => null]);

        $component = Livewire::actingAs($admin)
            ->test(ManageEventSettings::class);

        // 1. Open with duration 30 minutes
        $component->call('openManufacturerWithDuration', $mfg->id, 30);
        $mfg->refresh();
        $this->assertTrue($mfg->is_ordering_active);
        $this->assertNotNull($mfg->session_end_at);
        $this->assertTrue($mfg->session_end_at->gt(now()->addMinutes(25)));

        // 2. Extend by 15 minutes
        $oldEnd = $mfg->session_end_at;
        $component->call('extendManufacturer', $mfg->id, 15);
        $mfg->refresh();
        $this->assertTrue($mfg->session_end_at->gt($oldEnd));

        // 3. Close manufacturer
        $component->call('closeManufacturer', $mfg->id);
        $mfg->refresh();
        $this->assertFalse($mfg->is_ordering_active);

        // 4. Open unlimited
        $component->call('openManufacturerUnlimited', $mfg->id);
        $mfg->refresh();
        $this->assertTrue($mfg->is_ordering_active);
        $this->assertNull($mfg->session_end_at);

        // 5. Close all action
        $component->callAction('close_all_manufacturers');
        $mfg->refresh();
        $this->assertFalse($mfg->is_ordering_active);
    }

    public function test_checkout_validates_min_order_and_step_for_flucadex(): void
    {
        $flucadex = Product::create([
            'manufacturer_id' => $this->product->manufacturer_id,
            'sku' => 'GRH-018',
            'name' => 'FLUCADEX SYR',
            'uom' => 'FLS',
            'price' => 18000,
            'stock' => 999,
            'min_order' => 24,
            'step' => 24,
            'is_active' => true,
        ]);

        // 1. Order below min_order (12 fls)
        $resMin = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->post('/checkout', [
                'items' => [
                    ['product_id' => $flucadex->id, 'quantity' => 12],
                ],
            ]);
        $resMin->assertSessionHasErrors('error');

        // 2. Order not a multiple of step (25 fls)
        $resStep = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->post('/checkout', [
                'items' => [
                    ['product_id' => $flucadex->id, 'quantity' => 25],
                ],
            ]);
        $resStep->assertSessionHasErrors('error');

        // 3. Valid order: 24 fls (1 paket)
        $resValid = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->post('/checkout', [
                'items' => [
                    ['product_id' => $flucadex->id, 'quantity' => 24],
                ],
            ]);
        $resValid->assertSessionHasNoErrors();
        $resValid->assertRedirect();
    }

    public function test_graha_farma_grand_prize_allocation_and_leaderboard(): void
    {
        RewardCalculationService::clearGrandPrizeCache();

        $graha = Manufacturer::create([
            'name' => 'PT. GRAHA FARMA',
            'slug' => 'graha-farma',
            'is_active' => true,
        ]);

        $flucadex = Product::create([
            'manufacturer_id' => $graha->id,
            'sku' => 'GRH-018',
            'name' => 'FLUCADEX SYR',
            'uom' => 'FLS',
            'price' => 18000,
            'min_order' => 24,
            'step' => 24,
            'is_active' => true,
        ]);

        $pharmacyA = Pharmacy::create(['name' => 'Apotek Juara Satu', 'customer_code' => 'TEST-GP1']);
        $pharmacyB = Pharmacy::create(['name' => 'Apotek Juara Dua', 'customer_code' => 'TEST-GP2']);
        $pharmacyC = Pharmacy::create(['name' => 'Apotek Juara Tiga', 'customer_code' => 'TEST-GP3']);
        $pharmacyD = Pharmacy::create(['name' => 'Apotek Tanpa Hadiah', 'customer_code' => 'TEST-GP4']);

        // Pharmacy A: 297 box (3 paket)
        $orderA = Order::create(['order_number' => 'PO-GP-001', 'pharmacy_id' => $pharmacyA->id, 'total_amount' => 5346000, 'total_items' => 297, 'status' => 'confirmed']);
        OrderItem::create(['order_id' => $orderA->id, 'product_id' => $flucadex->id, 'product_name' => $flucadex->name, 'manufacturer_name' => $graha->name, 'price' => 18000, 'quantity' => 297, 'subtotal' => 5346000]);

        // Pharmacy B: 198 box (2 paket)
        $orderB = Order::create(['order_number' => 'PO-GP-002', 'pharmacy_id' => $pharmacyB->id, 'total_amount' => 3564000, 'total_items' => 198, 'status' => 'confirmed']);
        OrderItem::create(['order_id' => $orderB->id, 'product_id' => $flucadex->id, 'product_name' => $flucadex->name, 'manufacturer_name' => $graha->name, 'price' => 18000, 'quantity' => 198, 'subtotal' => 3564000]);

        // Pharmacy C: 99 box (1 paket)
        $orderC = Order::create(['order_number' => 'PO-GP-003', 'pharmacy_id' => $pharmacyC->id, 'total_amount' => 1782000, 'total_items' => 99, 'status' => 'confirmed']);
        OrderItem::create(['order_id' => $orderC->id, 'product_id' => $flucadex->id, 'product_name' => $flucadex->name, 'manufacturer_name' => $graha->name, 'price' => 18000, 'quantity' => 99, 'subtotal' => 1782000]);

        // Pharmacy D: 48 box (0 paket)
        $orderD = Order::create(['order_number' => 'PO-GP-004', 'pharmacy_id' => $pharmacyD->id, 'total_amount' => 864000, 'total_items' => 48, 'status' => 'confirmed']);
        OrderItem::create(['order_id' => $orderD->id, 'product_id' => $flucadex->id, 'product_name' => $flucadex->name, 'manufacturer_name' => $graha->name, 'price' => 18000, 'quantity' => 48, 'subtotal' => 864000]);

        $gatheringService = app(GatheringService::class);
        $rewardService = app(RewardCalculationService::class);

        // Verify Leaderboard Graha Farma
        $lb = $gatheringService->getLeaderboard($graha->id);
        $this->assertEquals($pharmacyA->id, $lb[0]['id']);
        $this->assertEquals('1 Unit Smart TV Polytron 32 Inch', $lb[0]['grand_prize']);
        $this->assertEquals(3, $lb[0]['paket_count']);

        $this->assertEquals($pharmacyB->id, $lb[1]['id']);
        $this->assertEquals('1 Unit Dispenser Cosmos Galon Bawah', $lb[1]['grand_prize']);
        $this->assertEquals(2, $lb[1]['paket_count']);

        $this->assertEquals($pharmacyC->id, $lb[2]['id']);
        $this->assertEquals('1 Unit Kipas Angin Cosmos', $lb[2]['grand_prize']);
        $this->assertEquals(1, $lb[2]['paket_count']);

        $this->assertNull($lb[3]['grand_prize'] ?? null);

        // Verify RewardCalculationService physical_rewards
        $rewardsA = $rewardService->calculateForPharmacy($pharmacyA);
        $this->assertArrayHasKey('1 Unit Smart TV Polytron 32 Inch (Grand Prize Graha Farma #1)', $rewardsA['physical_rewards']);

        $rewardsB = $rewardService->calculateForPharmacy($pharmacyB);
        $this->assertArrayHasKey('1 Unit Dispenser Cosmos Galon Bawah (Grand Prize Graha Farma #2)', $rewardsB['physical_rewards']);

        $rewardsC = $rewardService->calculateForPharmacy($pharmacyC);
        $this->assertArrayHasKey('1 Unit Kipas Angin Cosmos (Grand Prize Graha Farma #3)', $rewardsC['physical_rewards']);

        $rewardsD = $rewardService->calculateForPharmacy($pharmacyD);
        $this->assertEmpty($rewardsD['physical_rewards']);
    }

    public function test_follow_up_order_only_unlocks_choices_for_products_in_cart(): void
    {
        $exeltis = Manufacturer::create([
            'name' => 'EXELTIS',
            'slug' => 'exeltis',
            'is_active' => true,
        ]);

        $lanso = Product::create([
            'manufacturer_id' => $exeltis->id,
            'sku' => 'EXE-001',
            'name' => 'Lansoprazole 30mg',
            'uom' => 'Box',
            'price' => 10000,
            'is_active' => true,
        ]);

        $sucra = Product::create([
            'manufacturer_id' => $exeltis->id,
            'sku' => 'EXE-002',
            'name' => 'Sucralfate 60ml',
            'uom' => 'Botol',
            'price' => 9500,
            'is_active' => true,
        ]);

        // PO 1: 200 box Lansoprazole
        $order1 = Order::create([
            'order_number' => 'PO-TEST-0001',
            'pharmacy_id' => $this->pharmacy->id,
            'total_amount' => 2000000,
            'total_items' => 200,
            'status' => 'confirmed',
            'reward_selections' => [
                'exeltis_lanso' => 'Panci Set 3in 1 (Tier 200 Box)',
            ],
        ]);
        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $lanso->id,
            'product_name' => $lanso->name,
            'manufacturer_name' => $exeltis->name,
            'price' => 10000,
            'quantity' => 200,
            'subtotal' => 2000000,
        ]);

        // Cart for PO 2: ONLY 150 botol Sucralfate (no Lansoprazole in this cart!)
        $rewardService = app(RewardCalculationService::class);
        $preview = $rewardService->evaluateCart([
            [
                'product_id' => $sucra->id,
                'quantity' => 150,
                'price' => 9500,
                'name' => $sucra->name,
            ],
        ], $this->pharmacy);

        // Lansoprazole choice count must be 0 for this cart!
        $this->assertEquals(0, $preview['unlocked_now']['exeltis_lanso_choice_count']);
        $this->assertEquals(0, $preview['unlocked_now']['exeltis_lanso_qty']);

        // Sucralfate choice count must be 1 with 150 qty!
        $this->assertEquals(1, $preview['unlocked_now']['exeltis_sucra_choice_count']);
        $this->assertEquals(150, $preview['unlocked_now']['exeltis_sucra_qty']);
        $this->assertNotEmpty($preview['unlocked_now']['exeltis_sucra_options']);
    }

    public function test_leaderboard_micro_cache_invalidates_immediately_on_new_order(): void
    {
        $service = app(GatheringService::class);

        // Prime cache
        $initial = $service->getAllLeaderboards();
        $entryBefore = $initial['overall']->firstWhere('id', $this->pharmacy->id);
        $this->assertEquals(0, $entryBefore['total_spending']);
        $this->assertEquals(0, $initial['total_gmv']);

        // Create an order via checkout
        $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->post('/checkout', [
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 4,
                    ],
                ],
            ])
            ->assertRedirect();

        // Subsequent call must immediately reflect new spending without waiting for TTL
        $after = $service->getAllLeaderboards();
        $entryAfter = $after['overall']->firstWhere('id', $this->pharmacy->id);
        $this->assertEquals(800000, $entryAfter['total_spending']);
        $this->assertEquals(800000, $after['total_gmv']);
        $this->assertEquals(1, $after['total_orders']);
    }
}
