<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('stock_mutations', function (Blueprint $table) {
        // Cek dulu apakah kolom sudah ada untuk menghindari error "duplicate column"
        if (!Schema::hasColumn('stock_mutations', 'customer_id')) {
            // Gunakan nullable agar transaksi lama yang belum ada customernya tidak error
            $table->unsignedBigInteger('customer_id')->nullable()->after('id');
            
            // Definisikan foreign key secara terpisah agar lebih fleksibel
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('customers')
                  ->onDelete('set null');
        }

        if (!Schema::hasColumn('stock_mutations', 'sub_type')) {
            $table->string('sub_type')->nullable()->after('type');
        }
    });
}

public function down(): void
{
    Schema::table('stock_mutations', function (Blueprint $table) {
        $table->dropForeign(['customer_id']);
        $table->dropColumn('customer_id');
        $table->dropColumn('sub_type');
    });
}
};
