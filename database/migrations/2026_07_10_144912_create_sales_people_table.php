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
        Schema::create('sales_people', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('phone')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });

    // 2. Tambahkan kolom sales_id ke tabel tempat transaksi disimpan
    // Karena sistem Anda menyimpan penjualan di stock_mutations, maka tambahkan ke sini:
    Schema::table('stock_mutations', function (Blueprint $table) {
        $table->foreignId('sales_id')->nullable()->constrained('sales_people');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_people');
    }
};
