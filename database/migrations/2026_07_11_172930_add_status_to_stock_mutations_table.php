<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('stock_mutations', function (Blueprint $table) {
        // Menambahkan kolom status, default-nya kita set 'RECEIVED' 
        // agar data lama tidak error
        //$table->string('status')->default('RECEIVED')->after('sub_type');
    });
}

public function down(): void
{
    Schema::table('stock_mutations', function (Blueprint $table) {
        $table->dropColumn('status');
    });
}
};
