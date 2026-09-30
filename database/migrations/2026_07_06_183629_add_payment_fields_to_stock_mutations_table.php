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
        $table->string('transaction_type')->default('pembelian')->after('type'); // 'pembelian' atau 'mutasi'
        $table->string('payment_status')->nullable()->after('transaction_type'); // 'lunas' atau 'hutang'
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('stock_mutations', function (Blueprint $table) {
        $table->dropColumn(['transaction_type', 'payment_status']);
    });
}
};
