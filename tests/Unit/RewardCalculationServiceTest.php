<?php

namespace Tests\Unit;

use App\Services\RewardCalculationService;
use PHPUnit\Framework\TestCase;

class RewardCalculationServiceTest extends TestCase
{
    protected RewardCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RewardCalculationService;
    }

    public function test_zenith_single_and_multi_package(): void
    {
        // 1 Paket (1.5jt)
        $res1 = $this->service->evaluateFromAggregates(
            ['zenith-bufa' => 1500000],
            []
        );
        $this->assertEquals(1, $res1['physical_rewards']['Tumbler (Zenith Paket)']);
        $this->assertArrayNotHasKey('Sarung (Zenith Paket)', $res1['physical_rewards']);

        // 2 Paket (3jt)
        $res2 = $this->service->evaluateFromAggregates(
            ['zenith-bufa' => 3000000],
            []
        );
        $this->assertEquals(2, $res2['physical_rewards']['Tumbler (Zenith Paket)']);
        $this->assertEquals(2, $res2['physical_rewards']['Sarung (Zenith Paket)']);
    }

    public function test_zenith_product_specific_bonuses(): void
    {
        $res = $this->service->evaluateFromAggregates(
            ['zenith-bufa' => 5000000],
            [
                'ZNT-021' => 100, // 2x Chopper (Flumin)
                'ZNT-045' => 180, // 1x Wajan Serbaguna (Zenpepsa)
                'ZNT-018' => 150, // 2x Electric Cooker (Flamifa)
            ]
        );

        $this->assertEquals(2, $res['physical_rewards']['Chopper (Flumin Plus)']);
        $this->assertEquals(1, $res['physical_rewards']['Wajan Serbaguna (Zenpepsa)']);
        $this->assertEquals(2, $res['physical_rewards']['Electric Cooker (Flamifa)']);
    }

    public function test_graha_farma_package_vouchers_and_neo_protifed(): void
    {
        // 1 Paket (99 Box reguler) -> 100.000
        $res1 = $this->service->evaluateFromAggregates(
            ['graha-farma' => 3500000],
            ['GRH-001' => 99]
        );
        $this->assertEquals(100000, $res1['vouchers_by_manufacturer']['graha-farma']);

        // 3 Paket (297 Box reguler) -> 500.000
        $res3 = $this->service->evaluateFromAggregates(
            ['graha-farma' => 10000000],
            ['GRH-001' => 297]
        );
        $this->assertEquals(500000, $res3['vouchers_by_manufacturer']['graha-farma']);

        // Neo Protifed 25 box -> 50.000 voucher + 1 choice
        $resNeo = $this->service->evaluateFromAggregates(
            ['graha-farma' => 1250000],
            ['GRH-019' => 25]
        );
        $this->assertEquals(50000, $resNeo['vouchers_by_manufacturer']['graha-farma']);
        $this->assertEquals(1, $resNeo['choice_quotas']['graha_neo']);
    }

    public function test_mepro_individual_vouchers_and_bundle_magic_com(): void
    {
        // Only 50 Cefixime -> 100.000 voucher
        $res1 = $this->service->evaluateFromAggregates(
            ['mepro' => 3550000],
            ['MEP-001' => 50]
        );
        $this->assertEquals(100000, $res1['vouchers_by_manufacturer']['mepro']);
        $this->assertArrayNotHasKey('Magic Com (Mepro Bundle)', $res1['physical_rewards']);

        // All 4 products @ 50 box -> 350.000 voucher + 1 Magic Com
        $resAll = $this->service->evaluateFromAggregates(
            ['mepro' => 14000000],
            [
                'MEP-001' => 50, // Cefixime: 100k
                'MEP-002' => 50, // Cefadroxil: 100k
                'MEP-003' => 50, // Mezac: 50k
                'MEP-004' => 50, // Intunal: 100k
            ]
        );
        $this->assertEquals(350000, $resAll['vouchers_by_manufacturer']['mepro']);
        $this->assertEquals(1, $resAll['physical_rewards']['Magic Com (Mepro Bundle)']);
    }

    public function test_interbat_choice_and_progressive_voucher(): void
    {
        // 1 Paket (2.5jt) -> 1 choice quota
        $res1 = $this->service->evaluateFromAggregates(['interbat' => 2500000], []);
        $this->assertEquals(1, $res1['choice_quotas']['interbat']);
        $this->assertArrayNotHasKey('interbat', $res1['vouchers_by_manufacturer']);

        // 2 Paket (5jt) -> 2 choices + 50.000 voucher
        $res2 = $this->service->evaluateFromAggregates(['interbat' => 5000000], []);
        $this->assertEquals(2, $res2['choice_quotas']['interbat']);
        $this->assertEquals(50000, $res2['vouchers_by_manufacturer']['interbat']);

        // 3 Paket (7.5jt) -> 3 choices + 125.000 voucher
        $res3 = $this->service->evaluateFromAggregates(['interbat' => 7500000], []);
        $this->assertEquals(3, $res3['choice_quotas']['interbat']);
        $this->assertEquals(125000, $res3['vouchers_by_manufacturer']['interbat']);
    }

    public function test_other_manufacturers_rewards(): void
    {
        $res = $this->service->evaluateFromAggregates(
            [
                'erita' => 1200000,
                'arto-farma' => 1450000,
                'yarindo' => 2223000,
                'dexa' => 2400000,
            ],
            [
                'ERT-001' => 100, // 1 Rice Cooker
                'ART-001' => 100, // 1 Kipas Angin Sogo
                'YAR-001' => 78,  // 1 Kain Batik
                'DXA-001' => 100, // 1 Mug + 50k
            ]
        );

        $this->assertEquals(1, $res['physical_rewards']['Rice Cooker (Erita)']);
        $this->assertEquals(1, $res['physical_rewards']['Kipas Angin Sogo (Arto Farma)']);
        $this->assertEquals(1, $res['physical_rewards']['Kain Batik (Yarindo)']);
        $this->assertEquals(1, $res['physical_rewards']['Tumbler/Mug (Dexa)']);
        $this->assertEquals(50000, $res['vouchers_by_manufacturer']['dexa']);
    }

    public function test_exeltis_box_and_bottle_tracking(): void
    {
        $res = $this->service->evaluateFromAggregates(
            ['exeltis' => 1000000],
            [
                'EXE-001' => 50, // 50 Box Lanso
                'EXE-002' => 30, // 30 Fls Sucra 60ml
                'EXE-003' => 20, // 20 Fls Sucra 100ml
            ]
        );

        $this->assertEquals(50, $res['details']['exeltis']['lanso_box']);
        $this->assertEquals(50, $res['details']['exeltis']['sucra_fls']);
        $this->assertEquals('ANDA BEBAS MEMILIH HADIAH YANG ADA DI PROGRAM', $res['details']['exeltis']['program_note']);
    }

    public function test_exeltis_tiered_choice_options_and_custom_selection(): void
    {
        $optionsLanso = RewardCalculationService::getExeltisLansoOptions(50);
        $this->assertCount(8, $optionsLanso); // tiers: 5, 10, 15, 20, 25, 30, 40, 50
        $this->assertStringContainsString('Speaker Aktif', $optionsLanso[0]); // highest first
        $this->assertStringContainsString('Handuk 30 x 70cm', end($optionsLanso));

        // Test custom selection: user unlocked 50 Box, but deliberately chose Tier 25 (Wajan Hitam)
        $res = $this->service->evaluateFromAggregates(
            ['exeltis' => 1000000],
            ['EXE-001' => 50],
            [
                ['exeltis_lanso' => 'Wajan Hitam 24cm (Tier 25 Box)'],
            ]
        );

        $this->assertCount(1, $res['choice_selections']);
        $this->assertEquals('Wajan Hitam 24cm (Tier 25 Box)', $res['choice_selections'][0]['selected_option']);
        $this->assertEquals('exeltis', $res['choice_selections'][0]['manufacturer_slug']);
    }
}
