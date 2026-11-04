<?php

namespace Tests\Feature;

use App\Models\EventSetting;
use App\Models\Manufacturer;
use App\Models\Pharmacy;
use App\Models\Product;
use Database\Seeders\GatheringSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataSeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_gathering_seeder_populates_exact_master_data(): void
    {
        $this->seed(GatheringSeeder::class);

        // 1. Check Pharmacies (72 Peserta Gala Lunch)
        $this->assertEquals(72, Pharmacy::count());
        $this->assertDatabaseHas('pharmacies', [
            'customer_code' => 'SMS-001',
            'name' => 'Klinik As Syifa Medika',
        ]);
        $this->assertDatabaseHas('pharmacies', [
            'customer_code' => 'SMS-072',
            'name' => 'Apotek Konang Grujugan',
        ]);

        // 2. Check Manufacturers (9 Pabrikan)
        $this->assertEquals(9, Manufacturer::count());
        $expectedSlugs = [
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
        foreach ($expectedSlugs as $slug) {
            $this->assertDatabaseHas('manufacturers', ['slug' => $slug]);
        }

        // 3. Check Products (92 SKU, All Active)
        $this->assertEquals(92, Product::count());
        $this->assertEquals(92, Product::where('is_active', true)->count());

        // Updated Zenith Products with official prices
        $this->assertDatabaseHas('products', [
            'name' => 'RHEFICAM TAB',
            'is_active' => true,
            'price' => 25000,
        ]);
        $this->assertDatabaseHas('products', [
            'name' => 'ZENTRISON 4MG TAB',
            'is_active' => true,
            'price' => 44500,
        ]);

        // Graha Farma Products Check
        $this->assertDatabaseHas('products', [
            'name' => 'GRAFAMIC',
            'price' => 36000,
        ]);
        $this->assertDatabaseHas('products', [
            'name' => 'NEO PROTIFED ED 04/27',
            'price' => 50000,
        ]);

        // Mepro Intunal discounted price
        $this->assertDatabaseHas('products', [
            'name' => 'INTUNAL FORTE disk. 40%',
            'price' => 63600,
        ]);

        // 4. Event Settings
        $setting = EventSetting::first();
        $this->assertNotNull($setting);
        $this->assertTrue($setting->is_ordering_active);
    }

    public function test_onboarding_loads_all_seeded_pharmacies(): void
    {
        $this->seed(GatheringSeeder::class);

        $response = $this->get('/');

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Guest/Onboarding')
            ->has('samplePharmacies', 72)
        );
    }
}
