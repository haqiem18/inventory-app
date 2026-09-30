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
        // Perbaikan: mengubah 'table $table' menjadi 'Blueprint $table'
        Schema::table('stock_mutations', function (Blueprint $table) {
            $table->string('sub_type')->nullable()->after('type'); // 'produksi' atau 'mutasi'
            $table->foreignId('to_branch_id')->nullable()->constrained('branches')->nullOnDelete()->after('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_mutations', function (Blueprint $table) {
            // Drop foreign key terlebih dahulu sebelum menghapus kolomnya
            $table->dropForeign(['to_branch_id']);
            
            // Hapus kolom yang telah ditambahkan
            $table->dropColumn(['sub_type', 'to_branch_id']);
        });
    }
};