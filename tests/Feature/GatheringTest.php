<?php

namespace Tests\Feature;

use App\Models\EventSetting;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\RewardTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'table_number' => 'Meja 08',
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

    public function test_guest_can_view_catalog_when_logged_in(): void
    {
        $response = $this->withSession(['guest_pharmacy_id' => $this->pharmacy->id])
            ->get('/katalog');

        $response->assertStatus(200);
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
        $response->assertRedirect('/order/sukses/' . $order->id);
    }

    public function test_stage_leaderboard_page_and_api(): void
    {
        $response = $this->get('/stage');
        $response->assertStatus(200);

        $apiResponse = $this->getJson('/api/stage/data');
        $apiResponse->assertStatus(200)
            ->assertJsonStructure([
                'leaderboard',
                'stats' => ['total_gmv', 'total_orders'],
                'event',
            ]);
    }
}
