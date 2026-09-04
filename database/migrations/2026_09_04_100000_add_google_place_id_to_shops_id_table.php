<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops_id', function (Blueprint $table) {
            $table->string('google_place_id', 255)->nullable()->after('map_iframe');
        });
    }

    public function down(): void
    {
        Schema::table('shops_id', function (Blueprint $table) {
            $table->dropColumn('google_place_id');
        });
    }
};
