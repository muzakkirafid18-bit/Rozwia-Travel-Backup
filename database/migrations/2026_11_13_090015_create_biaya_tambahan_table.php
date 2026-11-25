<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biaya_tambahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_jamaah_detail')->constrained('jamaah_details')->cascadeOnDelete();
            $table->text('keterangan');
            $table->decimal('nominal_biaya', 15, 2);
            $table->enum('status', [
                'Menunggu_Persetujuan',
                'Disetujui',
                'Ditolak',
                'Dibatalkan',
            ])->default('Menunggu_Persetujuan');
            $table->text('admin_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biaya_tambahan');
    }
};
