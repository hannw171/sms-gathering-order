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
        Schema::table('manufacturers', function (Blueprint $table) {
            $table->boolean('is_ordering_active')->default(false)->after('is_active');
            $table->dateTime('session_end_at')->nullable()->after('is_ordering_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manufacturers', function (Blueprint $table) {
            $table->dropColumn(['is_ordering_active', 'session_end_at']);
        });
    }
};
