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
            // Tambahkan kolom paid_amount. 
            // Sesuaikan 'after' dengan kolom yang ada di database Anda
            $table->decimal('paid_amount', 15, 2)->default(0)->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('stock_mutations', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });
    }
};
