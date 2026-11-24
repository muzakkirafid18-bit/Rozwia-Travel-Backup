<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotel', function (Blueprint $table) {
            $table->string('foto_hotel')->nullable()->after('alamat_hotel');
        });
    }

    public function down(): void
    {
        Schema::table('hotel', function (Blueprint $table) {
            $table->dropColumn('foto_hotel');
        });
    }
};
