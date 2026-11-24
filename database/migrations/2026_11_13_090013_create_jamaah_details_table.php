<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jamaah_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_booking')->constrained('bookings')->cascadeOnDelete();
            $table->string('nama_lengkap');
            $table->string('nik', 20)->index();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->text('alamat');
            $table->string('email')->nullable();
            $table->string('nomor_telepon', 25)->nullable();
            $table->string('passport_number', 30)->nullable()->index();
            $table->string('manifest_room', 50)->nullable();
            $table->string('seat_number', 20)->nullable();
            $table->string('manifest_bus', 50)->nullable();
            $table->enum('tracking_status', [
                'Persiapan',
                'Siap_Berangkat',
                'Dalam_Penerbangan',
                'Tiba_di_Arab_Saudi',
                'Pelaksanaan_Ibadah',
                'Perjalanan_Pulang',
                'Tiba_di_Indonesia',
                'Selesai',
            ])->default('Persiapan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jamaah_details');
    }
};
