<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom tempat_kumpul & tanggal_kumpul di tabel jadwal
        Schema::table('jadwal', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal', 'tempat_kumpul')) {
                $table->string('tempat_kumpul')->nullable()->after('nomor_penerbangan');
            }
            if (!Schema::hasColumn('jadwal', 'tanggal_kumpul')) {
                $table->dateTime('tanggal_kumpul')->nullable()->after('tempat_kumpul');
            }
        });

        // 2. Hapus kolom seat_number, manifest_bus, manifest_room, dan tracking_status di tabel jamaah_details
        Schema::table('jamaah_details', function (Blueprint $table) {
            $dropCols = [];
            foreach (['seat_number', 'manifest_bus', 'manifest_room', 'tracking_status'] as $col) {
                if (Schema::hasColumn('jamaah_details', $col)) {
                    $dropCols[] = $col;
                }
            }
            if (!empty($dropCols)) {
                $table->dropColumn($dropCols);
            }
        });

        // 3. Ubah tabel rekening_perusahaan menjadi tabel perusahaan
        if (Schema::hasTable('rekening_perusahaan') && !Schema::hasTable('perusahaan')) {
            // Karena tabel invoices memiliki foreign key ke rekening_perusahaan,
            // kita buat tabel perusahaan baru dan salin datanya, atau rename tabel.
            // Di MySQL / MariaDB, kita bisa buat tabel perusahaan.
            Schema::create('perusahaan', function (Blueprint $table) {
                $table->id();
                $table->string('nama_perusahaan')->default('Rozwia Islamic Travel');
                $table->string('email_perusahaan')->default('rozwia@gmail.com');
                $table->string('telepon_perusahaan')->default('0895-1882-9438');
                $table->text('alamat_perusahaan')->nullable();
                $table->string('nama_bank')->default('Bank Syariah Indonesia');
                $table->string('nomor_rekening')->default('7123456789');
                $table->string('atas_nama')->default('PT Rozwia Islamic Travel');
                $table->string('no_izin')->nullable()->default('PPIU No. 1023/2021');
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
            });

            // Salin data lama jika ada
            $existing = DB::table('rekening_perusahaan')->first();
            if ($existing) {
                DB::table('perusahaan')->insert([
                    'id'                => $existing->id,
                    'nama_perusahaan'   => $existing->nama_perusahaan ?? 'Rozwia Islamic Travel',
                    'email_perusahaan'  => 'rozwia@gmail.com',
                    'telepon_perusahaan'=> '0895-1882-9438',
                    'alamat_perusahaan' => $existing->alamat_perusahaan ?? 'Graha Kartika Pratama Blok F2 No. 11 Bojong Baru, Bojong Gede, Bogor',
                    'nama_bank'         => $existing->nama_bank ?? 'Bank Syariah Indonesia',
                    'nomor_rekening'    => $existing->nomor_rekening ?? '7123456789',
                    'atas_nama'         => $existing->atas_nama ?? 'PT Rozwia Islamic Travel',
                    'no_izin'           => 'PPIU No. 1023/2021',
                    'status'            => $existing->status ?? 'active',
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            } else {
                DB::table('perusahaan')->insert([
                    'nama_perusahaan'   => 'Rozwia Islamic Travel',
                    'email_perusahaan'  => 'rozwia@gmail.com',
                    'telepon_perusahaan'=> '0895-1882-9438',
                    'alamat_perusahaan' => 'Graha Kartika Pratama Blok F2 No. 11 Bojong Baru, Bojong Gede, Bogor',
                    'nama_bank'         => 'Bank Syariah Indonesia',
                    'nomor_rekening'    => '7123456789',
                    'atas_nama'         => 'PT Rozwia Islamic Travel',
                    'no_izin'           => 'PPIU No. 1023/2021',
                    'status'            => 'active',
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            $table->dropColumn(['tempat_kumpul', 'tanggal_kumpul']);
        });

        Schema::table('jamaah_details', function (Blueprint $table) {
            $table->string('manifest_room', 50)->nullable();
            $table->string('seat_number', 20)->nullable();
            $table->string('manifest_bus', 50)->nullable();
            $table->string('tracking_status')->default('Persiapan');
        });

        Schema::dropIfExists('perusahaan');
    }
};
