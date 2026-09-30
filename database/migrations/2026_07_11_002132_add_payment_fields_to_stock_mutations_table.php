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
        // Menambahkan kolom yang dibutuhkan oleh Observer
       // $table->decimal('paid_amount', 15, 2)->default(0)->nullable();
        // $table->string('payment_status')->nullable(); 
    });
}

public function down(): void
{
    Schema::table('stock_mutations', function (Blueprint $table) {
        $table->dropColumn(['paid_amount', 'payment_status']);
    });
}
};
