<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Real 1С order gateway (ws_amo.1cws): CreateClientPoint creates a
        // "торговая точка" per customer. For a registered account we cache
        // the returned ClientPointCode here so repeat orders reuse it
        // instead of minting a new 1С client point every time. Guest
        // checkouts (no front_user row) have nowhere to cache this and get
        // a fresh client point per order — an accepted limitation of guest
        // checkout having no persistent identity, same as the rest of the
        // site treats guests.
        if (Schema::hasColumn('front_user', 'onec_client_point_code')) {
            return;
        }

        Schema::table('front_user', function (Blueprint $table) {
            $table->unsignedInteger('onec_client_point_code')->nullable()->after('gift_card');
        });
    }

    public function down(): void
    {
        Schema::table('front_user', function (Blueprint $table) {
            $table->dropColumn('onec_client_point_code');
        });
    }
};
