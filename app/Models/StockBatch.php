<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockBatch extends Model
{
    // Tentukan kolom apa saja yang bisa diisi
    protected $fillable = [
        'stock_mutation_id',
        'product_id',
        'quantity_initial',
        'quantity_remaining',
        'purchase_price',
        'status'
    ];
}
