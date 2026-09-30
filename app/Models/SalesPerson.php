<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesPerson extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
    ];

    // Fungsi mutations sekarang berada DI DALAM kelas
    public function mutations()
    {
        return $this->hasMany(StockMutation::class, 'sales_id');
    }
}