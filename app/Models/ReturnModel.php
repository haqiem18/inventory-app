<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnModel extends Model
{
    protected $table = 'returns';

    protected $guarded = [];

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->branch_id)) {
                $model->branch_id = auth()->user()->branch_id ?? 1;
            }
        });
    }
}