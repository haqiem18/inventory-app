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
        // Sesuaikan tipe data dengan kebutuhan (misal string)
        $table->string('status')->nullable()->after('price'); 
    });
}

public function down(): void
{
    Schema::table('stock_mutations', function (Blueprint $table) {
        $table->dropColumn('status');
    });
}
};
