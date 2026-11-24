<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('administrator', 'admin', 'supervisor', 'jamaah') NOT NULL DEFAULT 'jamaah'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 30)->default('jamaah')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::table('users')->where('role', 'administrator')->update(['role' => 'admin']);
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'supervisor', 'jamaah') NOT NULL DEFAULT 'jamaah'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'supervisor', 'jamaah'])->default('jamaah')->change();
            });
        }
    }
};
