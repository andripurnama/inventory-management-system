<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    public function purchases(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
