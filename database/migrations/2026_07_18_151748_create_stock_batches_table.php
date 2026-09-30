<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('purchase_price', 15, 2); // Harga beli saat batch ini masuk
            $table->integer('quantity_initial');      // Jumlah awal masuk
            $table->integer('quantity_remaining');    // Sisa stok yang masih ada
            $table->string('status')->default('active'); // Untuk keperluan Void
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};