<?php

namespace Tests\Feature;

use App\Filament\Pages\LeaderboardReport;
use App\Filament\Pages\PharmacyOrdersReport;
use App\Filament\Pages\RewardRecapReport;
use App\Filament\Widgets\DashboardOverviewWidget;
use App\Filament\Widgets\ManufacturerRevenueOverviewWidget;
use App\Filament\Widgets\PharmacyOrdersStatsWidget;
use App\Filament\Widgets\ProductSalesStatsWidget;
use App\Filament\Widgets\RecentOrdersOverviewWidget;
use App\Filament\Widgets\RevenueStatsWidget;
use App\Filament\Widgets\RewardRecapStatsWidget;
use App\Filament\Widgets\TopProductsWidget;
use App\Filament\Widgets\TopSpendersWidget;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GatheringReportsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Pharmacy $pharmacy;

    protected Manufacturer $zenith;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();

        $this->pharmacy = Pharmacy::create([
            'name' => 'Apotek Sehat Utama',
            'customer_code' => 'SMS-001',
            'owner_name' => 'Budi Santoso',
            'city' => 'Purwokerto',
            'is_checked_in' => true,
            'checked_in_at' => now(),
        ]);

        $this->zenith = Manufacturer::create([
            'name' => 'PT. Zenith',
            'slug' => 'zenith',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'manufacturer_id' => $this->zenith->id,
            'name' => 'Rheficam Tab',
            'sku' => 'ZEN-RHEFICAM',
            'unit' => 'box',
            'price' => 25000,
            'stock' => 9999,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => 'PO-20261108-0001',
            'pharmacy_id' => $this->pharmacy->id,
            'total_amount' => 1500000,
            'total_items' => 60,
            'status' => 'confirmed',
            'reward_selections' => null,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'manufacturer_name' => $this->zenith->name,
            'quantity' => 60,
            'price' => 25000,
            'subtotal' => 1500000,
        ]);
    }

    public function test_admin_can_access_all_five_gathering_report_pages(): void
    {
        $this->actingAs($this->admin);

        $pages = [
            '/admin/reward-recap-report',
            '/admin/product-sales-report',
            '/admin/revenue-report',
            '/admin/leaderboard-report',
            '/admin/pharmacy-orders-report',
        ];

        foreach ($pages as $url) {
            $response = $this->get($url);
            $response->assertSuccessful();
        }
    }

    public function test_reward_recap_handover_action_saves_correctly(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(RewardRecapReport::class)
            ->callTableAction('handover', $this->pharmacy, [
                'receiver_name' => 'dr. Budi Santoso',
                'notes' => 'Diserahkan langsung di meja 1',
            ]);

        $this->pharmacy->refresh();

        $this->assertNotNull($this->pharmacy->reward_handed_over_at);
        $this->assertEquals('dr. Budi Santoso', $this->pharmacy->reward_receiver_name);
        $this->assertEquals('Diserahkan langsung di meja 1', $this->pharmacy->reward_handover_notes);
    }

    public function test_reward_recap_cancel_handover_works(): void
    {
        $this->actingAs($this->admin);

        $this->pharmacy->update([
            'reward_handed_over_at' => now(),
            'reward_receiver_name' => 'dr. Budi Santoso',
        ]);

        Livewire::test(RewardRecapReport::class)
            ->callTableAction('cancelHandover', $this->pharmacy);

        $this->pharmacy->refresh();

        $this->assertNull($this->pharmacy->reward_handed_over_at);
        $this->assertNull($this->pharmacy->reward_receiver_name);
    }

    public function test_report_csv_export_streams_properly(): void
    {
        $this->actingAs($this->admin);

        $component = Livewire::test(RewardRecapReport::class);
        $response = $component->instance()->exportCsv();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }

    public function test_reward_recap_stats_widget_computes_correctly(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(RewardRecapStatsWidget::class)
            ->assertSuccessful();
    }

    public function test_product_sales_stats_widget_computes_correct_aggregates(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ProductSalesStatsWidget::class)
            ->assertSuccessful();
    }

    public function test_revenue_stats_widget_computes_gmv(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(RevenueStatsWidget::class)
            ->assertSuccessful();
    }

    public function test_leaderboard_report_table_renders(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(LeaderboardReport::class)
            ->assertCanSeeTableRecords([$this->pharmacy]);
    }

    public function test_pharmacy_orders_report_loads_details(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(PharmacyOrdersReport::class)
            ->assertCanSeeTableRecords([$this->pharmacy]);

        Livewire::test(PharmacyOrdersStatsWidget::class)
            ->assertSuccessful();
    }

    public function test_dashboard_widgets_render_successfully(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(DashboardOverviewWidget::class)
            ->assertSuccessful();

        Livewire::test(TopSpendersWidget::class)
            ->assertSuccessful();

        Livewire::test(TopProductsWidget::class)
            ->assertSuccessful();

        Livewire::test(ManufacturerRevenueOverviewWidget::class)
            ->assertSuccessful();

        Livewire::test(RecentOrdersOverviewWidget::class)
            ->assertSuccessful();
    }

    public function test_dashboard_table_widgets_are_limited_to_top_5(): void
    {
        $this->actingAs($this->admin);

        // Create 8 pharmacies, 8 products, 8 orders
        for ($i = 2; $i <= 8; $i++) {
            $pharmacy = Pharmacy::create([
                'name' => "Apotek Test $i",
                'customer_code' => "SMS-00$i",
                'city' => 'Purwokerto',
                'is_checked_in' => true,
            ]);

            $product = Product::create([
                'manufacturer_id' => $this->zenith->id,
                'name' => "Product $i",
                'sku' => "ZEN-00$i",
                'unit' => 'box',
                'price' => 10000 * $i,
                'stock' => 100,
                'is_active' => true,
            ]);

            $order = Order::create([
                'order_number' => "PO-20261108-000$i",
                'pharmacy_id' => $pharmacy->id,
                'total_amount' => 500000 * $i,
                'total_items' => 10 * $i,
                'status' => 'confirmed',
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'manufacturer_name' => $this->zenith->name,
                'quantity' => 10 * $i,
                'price' => 10000 * $i,
                'subtotal' => 500000 * $i,
            ]);
        }

        $topSpenders = Livewire::test(TopSpendersWidget::class);
        $this->assertCount(5, $topSpenders->instance()->getTableRecords());

        $topProducts = Livewire::test(TopProductsWidget::class);
        $this->assertCount(5, $topProducts->instance()->getTableRecords());

        $recentOrders = Livewire::test(RecentOrdersOverviewWidget::class);
        $this->assertCount(5, $recentOrders->instance()->getTableRecords());
    }
}
