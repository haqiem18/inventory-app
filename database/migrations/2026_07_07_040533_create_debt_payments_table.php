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
    Schema::create('debt_payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('stock_mutation_id')->constrained()->cascadeOnDelete(); // Menghubungkan ke nota hutang
        $table->decimal('amount_paid', 15, 2); // Jumlah yang dibayar
        $table->date('payment_date'); // Tanggal bayar
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debt_payments');
    }
};
