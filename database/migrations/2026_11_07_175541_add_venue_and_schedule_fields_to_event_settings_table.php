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
        Schema::table('event_settings', function (Blueprint $table) {
            $table->string('venue_name')->nullable()->default('Ballroom Grand Hotel • Meja Undangan')->after('event_name');
            $table->string('registration_schedule')->nullable()->default('Meja Registrasi Buka: 08:30 – 13:00 WIB')->after('venue_name');
            $table->string('registration_help_note')->nullable()->default('PIC Meja Bantuan siap melayani pencarian manual ID di Area Foyer Ballroom.')->after('registration_schedule');
            $table->dateTime('session_start_at')->nullable()->after('registration_help_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_settings', function (Blueprint $table) {
            $table->dropColumn([
                'venue_name',
                'registration_schedule',
                'registration_help_note',
                'session_start_at',
            ]);
        });
    }
};
