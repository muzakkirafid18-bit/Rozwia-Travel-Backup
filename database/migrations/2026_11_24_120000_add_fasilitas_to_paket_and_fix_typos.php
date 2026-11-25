<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('paket', 'fasilitas')) {
            Schema::table('paket', function (Blueprint $table) {
                $table->json('fasilitas')->nullable()->after('deskripsi');
            });
        }

        // Perbaiki typo "Pangrago" -> "Pangrango" jika ada di tabel perusahaan / rekening_perusahaan
        if (Schema::hasTable('perusahaan')) {
            DB::table('perusahaan')
                ->where('atas_nama', 'like', '%Pangrago%')
                ->get()
                ->each(function ($row) {
                    DB::table('perusahaan')
                        ->where('id', $row->id)
                        ->update(['atas_nama' => str_ireplace('Pangrago', 'Pangrango', $row->atas_nama)]);
                });
        }

        if (Schema::hasTable('rekening_perusahaan')) {
            DB::table('rekening_perusahaan')
                ->where('atas_nama', 'like', '%Pangrago%')
                ->get()
                ->each(function ($row) {
                    DB::table('rekening_perusahaan')
                        ->where('id', $row->id)
                        ->update(['atas_nama' => str_ireplace('Pangrago', 'Pangrango', $row->atas_nama)]);
                });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('paket', 'fasilitas')) {
            Schema::table('paket', function (Blueprint $table) {
                $table->dropColumn('fasilitas');
            });
        }
    }
};
