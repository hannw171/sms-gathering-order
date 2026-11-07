<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageEventSettings;
use App\Filament\Pages\RewardRecapReport;
use App\Models\EventSetting;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\RewardTier;
use App\Models\User;
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
}
