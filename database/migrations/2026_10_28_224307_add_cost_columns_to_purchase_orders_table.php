<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Tambahkan kolom diskon dan biaya tambahan jika belum ada
            if (!Schema::hasColumn('purchase_orders', 'discount')) {
                $table->decimal('discount', 15, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('purchase_orders', 'other_cost')) {
                $table->decimal('other_cost', 15, 2)->default(0)->after('discount');
            }
            if (!Schema::hasColumn('purchase_orders', 'shipping_cost')) {
                $table->decimal('shipping_cost', 15, 2)->default(0)->after('other_cost');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['discount', 'other_cost', 'shipping_cost']);
        });
    }
};
