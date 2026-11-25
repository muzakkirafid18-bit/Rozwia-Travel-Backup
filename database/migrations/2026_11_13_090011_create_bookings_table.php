<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('users')->restrictOnDelete();
            $table->foreignId('id_jadwal')->constrained('jadwal')->restrictOnDelete();
            $table->string('kode_booking', 50)->unique();
            $table->unsignedInteger('jumlah_jamaah')->default(1);
            $table->decimal('total_harga', 15, 2);
            $table->enum('status_booking', [
                'Menunggu_DP',
                'Menunggu_Pengumpulan_Dokumen',
                'Menunggu_Pelunasan',
                'Dikonfirmasi',
                'Selesai',
                'Dibatalkan',
                'Ditolak',
            ])->default('Menunggu_DP');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
