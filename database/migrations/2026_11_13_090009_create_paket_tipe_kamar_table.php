<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paket_tipe_kamar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_paket')->constrained('paket')->cascadeOnDelete();
            $table->foreignId('id_hotel')->constrained('hotel')->restrictOnDelete();
            $table->foreignId('id_tipe')->constrained('tipe_kamar')->restrictOnDelete();
            $table->decimal('harga', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paket_tipe_kamar');
    }
};
