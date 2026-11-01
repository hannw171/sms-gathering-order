<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Manufacturers / Principals
        Schema::create('manufacturers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Products / Katalog Obat
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manufacturer_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('uom')->default('Box'); // Satuan/Kemasan: 'Box @ 10 Strip', 'Botol 60ml'
            $table->decimal('price', 15, 2);
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->integer('stock')->default(999);
            $table->integer('min_order')->default(1);
            $table->integer('step')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Pharmacies / Tamu Apotek
        Schema::create('pharmacies', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Apotek Kimia Mandiri
            $table->string('customer_code')->unique(); // e.g. APT-001
            $table->string('table_number')->nullable(); // e.g. Meja 08
            $table->string('owner_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->string('sales_pic')->nullable();
            $table->string('device_session_token')->nullable();
            $table->boolean('is_checked_in')->default(false);
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
        });

        // 4. Reward Tiers
        Schema::create('reward_tiers', function (Blueprint $table) {
            $table->id();
            $table->integer('tier_level')->default(1);
            $table->string('name'); // e.g. Tier 1 - Logam Mulia 0.5g
            $table->decimal('min_spending', 15, 2); // Nilai target minimal belanja
            $table->string('reward_description'); // e.g. Logam Mulia 0.5g
            $table->string('badge_icon')->default('🏆');
            $table->timestamps();
        });

        // 5. Orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // e.g. PO-20261101-001
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_amount', 15, 2);
            $table->integer('total_items')->default(0);
            $table->foreignId('unlocked_reward_tier_id')->nullable()->constrained('reward_tiers')->nullOnDelete();
            $table->string('status')->default('confirmed'); // pending, confirmed, verified, cancelled
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        // 6. Order Items
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->string('manufacturer_name');
            $table->string('uom')->nullable();
            $table->decimal('price', 15, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });

        // 7. Event Settings
        Schema::create('event_settings', function (Blueprint $table) {
            $table->id();
            $table->string('event_name')->default('SMS Gathering & Order Night');
            $table->dateTime('session_end_at')->nullable(); // Countdown timer
            $table->boolean('is_ordering_active')->default(true);
            $table->boolean('is_leaderboard_visible')->default(true);
            $table->string('doorprize_announcement')->nullable();
            $table->text('running_ticker_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_settings');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('reward_tiers');
        Schema::dropIfExists('pharmacies');
        Schema::dropIfExists('products');
        Schema::dropIfExists('manufacturers');
    }
};
