<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'branch_id',
        'stock',
    ];

    /**
     * Relasi ke model Product (Barang/Kain)
     */
    
public function product()
{
    return $this->belongsTo(Product::class, 'product_id');
}
    /**
     * Relasi ke model Branch (Cabang Gudang)
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}