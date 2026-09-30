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
        $table->decimal('purchase_price', 15, 2)->nullable()->after('price'); 
        // 'price' di sini adalah harga jual yang sudah ada
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_mutations', function (Blueprint $table) {
            //
        });
    }
};
