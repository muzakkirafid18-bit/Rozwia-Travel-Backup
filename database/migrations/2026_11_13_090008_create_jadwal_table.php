<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_paket')->constrained('paket')->cascadeOnDelete();
            $table->foreignId('id_maskapai')->constrained('maskapai')->restrictOnDelete();
            $table->string('nomor_penerbangan');
            $table->date('tanggal_keberangkatan');
            $table->date('tanggal_pulang');
            $table->unsignedInteger('kuota')->default(45);
            $table->enum('status', ['Tersedia', 'Penuh'])->default('Tersedia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal');
    }
};
