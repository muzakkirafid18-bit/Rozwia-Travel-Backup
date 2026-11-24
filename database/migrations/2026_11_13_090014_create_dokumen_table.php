<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jamaah_detail_id')->constrained('jamaah_details')->cascadeOnDelete();
            $table->string('tipe_dokumen'); // Paspor, KTP, KK, Buku Nikah, Vaksin, dll.
            $table->string('file_path');
            $table->enum('status', [
                'Menunggu_Verifikasi',
                'Diverifikasi',
                'Perlu_Diperbaiki',
                'Ditolak',
            ])->default('Menunggu_Verifikasi');
            $table->text('admin_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen');
    }
};
