<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_kamar_booking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_booking')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('id_paket_tipe_kamar')->constrained('paket_tipe_kamar')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_kamar_booking');
    }
};
