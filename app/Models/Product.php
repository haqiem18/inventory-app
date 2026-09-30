<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_id',
        'brand_id',
        'unit_id',
        'name',
        'sku',
        'purchase_price',
        'selling_price',
        'min_stock',
    ];
    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function stockMutations() {
    return $this->hasMany(StockMutation::class);
}
}
