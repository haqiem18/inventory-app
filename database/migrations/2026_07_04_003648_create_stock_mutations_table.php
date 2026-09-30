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
    Schema::create('stock_mutations', function (Blueprint $table) {
        $table->id();
        
        // Nomor referensi transaksi otomatis (Contoh: TRX-IN-20260704-0001)
        $table->string('reference_number');
        
        // Alur Logistik: Masuk ke cabang mana? Berasal dari supplier mana?
        $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
        $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
        
        // Detail Barang & Jumlahnya
        $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
        $table->integer('quantity'); 
        
        // Jenis Mutasi (Sistem disiapkan untuk 'Masuk', namun fleksibel jika nanti ada 'Keluar' atau 'Penyesuaian')
        $table->string('type')->default('Masuk'); 
        
        // Catatan tambahan (misal: nomor nota fisik supplier atau keterangan kondisi barang)
        $table->text('notes')->nullable();
        
        // Tanggal operasional mutasi dilakukan
        $table->date('mutation_date');
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_mutations');
    }
};
