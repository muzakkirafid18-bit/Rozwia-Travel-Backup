<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rangkaian_ibadah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_paket')->constrained('paket')->cascadeOnDelete();
            $table->unsignedInteger('urutan')->default(1);
            $table->string('judul_tahapan');
            $table->text('deskripsi')->nullable();
            $table->string('path_icon')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rangkaian_ibadah');
    }
};
