<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('products')
            ->where('sku', 'GRH-018')
            ->update([
                'min_order' => 24,
                'step' => 24,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('products')
            ->where('sku', 'GRH-018')
            ->update([
                'min_order' => 1,
                'step' => 1,
            ]);
    }
};
