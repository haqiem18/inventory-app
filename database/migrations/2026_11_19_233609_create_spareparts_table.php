<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spareparts', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('mesin');
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->text('alasan_pergantian');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spareparts');
    }
};