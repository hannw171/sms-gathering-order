<?php

use App\Models\Order;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $orders = Order::whereNotNull('reward_selections')->with(['items.product.manufacturer'])->get();

        foreach ($orders as $order) {
            $raw = $order->reward_selections;
            if (! is_array($raw) || empty($raw)) {
                continue;
            }

            $orderSkus = $order->items->map(fn ($it) => $it->product?->sku)->filter()->toArray();
            $orderMfgs = $order->items->map(fn ($it) => $it->product?->manufacturer?->name ?? $it->manufacturer_name)->filter()->toArray();

            $hasLanso = in_array('EXE-001', $orderSkus);
            $hasSucra = in_array('EXE-002', $orderSkus) || in_array('EXE-003', $orderSkus);
            $hasNeo = in_array('GRH-019', $orderSkus);
            $hasInterbat = in_array('INTERBAT', $orderMfgs);

            $cleaned = [];
            foreach ($raw as $key => $val) {
                $kLower = strtolower((string) $key);
                if (str_contains($kLower, 'lanso') && ! $hasLanso) {
                    continue;
                }
                if (str_contains($kLower, 'sucra') && ! $hasSucra) {
                    continue;
                }
                if ((str_starts_with($kLower, 'graha_neo') || str_starts_with($kLower, 'neo_protifed')) && ! $hasNeo) {
                    continue;
                }
                if (str_starts_with($kLower, 'interbat') && ! $hasInterbat) {
                    continue;
                }
                $cleaned[$key] = $val;
            }

            if ($cleaned !== $raw) {
                $order->update([
                    'reward_selections' => ! empty($cleaned) ? $cleaned : null,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
