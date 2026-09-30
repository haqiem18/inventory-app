<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebtPayment extends Model
{
    protected $fillable = ['stock_mutation_id', 'amount_paid', 'payment_date'];

    // Relasi balik ke StockMutation
    public function stockMutation()
    {
        return $this->belongsTo(StockMutation::class);
    }
}
