<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_booking')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('id_rekening')->constrained('rekening_perusahaan')->restrictOnDelete();
            $table->string('invoice_number', 50)->unique();
            $table->decimal('amount', 15, 2);
            $table->dateTime('batas_pembayaran');
            $table->enum('status', [
                'Belum_Dibayar',
                'Sebagian',
                'Lunas',
                'Jatuh_Tempo',
                'Dibatalkan',
            ])->default('Belum_Dibayar');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
