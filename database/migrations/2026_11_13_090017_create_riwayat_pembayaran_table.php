<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_invoice')->constrained('invoices')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('metode_pembayaran')->default('Transfer Bank Manual');
            $table->string('bukti_transfer');
            $table->enum('status', [
                'Menunggu_Verifikasi',
                'Berhasil',
                'Ditolak',
                'Gagal',
                'Dikembalikan',
            ])->default('Menunggu_Verifikasi');
            $table->text('admin_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_pembayaran');
    }
};
