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
            if (Schema::hasColumn('pharmacies', 'table_number')) {
                $table->dropColumn('table_number');
            }
        });

        Schema::table('event_settings', function (Blueprint $table) {
            if (Schema::hasColumn('event_settings', 'show_table_number')) {
                $table->dropColumn('show_table_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->string('table_number')->nullable()->after('customer_code');
        });

        Schema::table('event_settings', function (Blueprint $table) {
            $table->boolean('show_table_number')->default(false)->after('is_leaderboard_visible');
        });
    }
};
