<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel', function (Blueprint $table) {
            $table->id();
            $table->string('nama_hotel');
            $table->unsignedTinyInteger('bintang_hotel')->default(4);
            $table->string('lokasi'); // Makkah / Madinah
            $table->text('alamat_hotel');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel');
    }
};
