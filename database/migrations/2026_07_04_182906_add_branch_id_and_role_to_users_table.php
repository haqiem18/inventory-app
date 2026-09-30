<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Menambahkan kolom branch_id (opsional untuk Super Admin)
            $table->foreignId('branch_id')
                ->nullable()
                ->after('password')
                ->constrained('branches')
                ->nullOnDelete();

            // Menambahkan kolom role
            $table->string('role')
                ->default('admin_cabang')
                ->after('branch_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['branch_id', 'role']);
        });
    }
};