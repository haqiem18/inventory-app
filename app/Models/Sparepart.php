<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sparepart extends Model
{
    protected $fillable = [
        'tanggal',
        'product_id',
        'mesin',
        'branch_id',
        'alasan_pergantian',
        'user_id',
        'quantity',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function teknisi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}