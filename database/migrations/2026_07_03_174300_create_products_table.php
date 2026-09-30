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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel Master Data yang sudah kamu buat sebelumnya
            $table->foreignId('type_id')->constrained('types')->onDelete('cascade');
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');

            // Informasi dasar produk
            $table->string('name');
            $table->string('sku')->unique(); // Kode unik barang (barcode/sku)
            // Acuan Keuangan & Aturan Stok (Gunakan decimal agar akurat untuk uang)
            $table->decimal('purchase_price', 15, 2)->default(0); // Harga modal acuan
            $table->decimal('selling_price', 15, 2)->default(0);  // Harga jual acuan
            $table->integer('min_stock')->default(0); // Batas minimal stok untuk alert/notifikasi

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
