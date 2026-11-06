<?php

namespace Tests\Feature;

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
}
