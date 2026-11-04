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
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->timestamp('reward_handed_over_at')->nullable()->after('last_active_at');
            $table->string('reward_receiver_name')->nullable()->after('reward_handed_over_at');
            $table->text('reward_handover_notes')->nullable()->after('reward_receiver_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->dropColumn([
                'reward_handed_over_at',
                'reward_receiver_name',
                'reward_handover_notes',
            ]);
        });
    }
};
