<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jamaah_details', function (Blueprint $table) {
            $table->string('status_kemenag', 30)->default('Belum Diajukan')->after('tracking_status');
            $table->string('status_visa', 30)->default('Belum Diajukan')->after('status_kemenag');
            $table->string('nomor_visa', 50)->nullable()->after('status_visa');
            $table->date('tanggal_terbit_visa')->nullable()->after('nomor_visa');
        });
    }

    public function down(): void
    {
        Schema::table('jamaah_details', function (Blueprint $table) {
            $table->dropColumn(['status_kemenag', 'status_visa', 'nomor_visa', 'tanggal_terbit_visa']);
        });
    }
};
