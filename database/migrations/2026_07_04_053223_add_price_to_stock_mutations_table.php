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
        // Menambahkan kolom harga beli per item dan total harga transaksi
        $table->decimal('price', 15, 2)->default(0)->after('quantity');
        $table->decimal('subtotal', 15, 2)->default(0)->after('price');
    });
}

public function down(): void
{
    Schema::table('stock_mutations', function (Blueprint $table) {
        $table->dropColumn(['price', 'subtotal']);
    });
}
};
