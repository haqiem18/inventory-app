<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnModel extends Model
{
    protected $table = 'returns';

    protected $guarded = [];

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->branch_id)) {
                $model->branch_id = auth()->user()->branch_id ?? 1;
            }
        });

        // Kalkulasi otomatis total_amount saat disimpan/di-update
        static::saving(function ($model) {
            if ($model->relationLoaded('items') || $model->exists) {
                $model->total_amount = $model->items->sum(function ($item) {
                    $qty = $item->quantity ?? 0;
                    $price = $item->price ?? 0;
                    return $qty * $price;
                });
            }
        });
    }
}