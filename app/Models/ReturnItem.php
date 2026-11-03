<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnItem extends Model
{
    protected $table = 'return_items';
    
    protected $guarded = [];

    public function returnTransaction(): BelongsTo
    {
        return $this->belongsTo(ReturnModel::class, 'return_id');
    }
}