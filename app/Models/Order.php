<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use sirajcse\UniqueIdGenerator\UniqueIdGenerator;

class Order extends Model
{
    use HasFactory;

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->order_reference = UniqueIdGenerator::generate(['table' => $model->getTable(), 'length' => 10, 'prefix' => 'ORD-', 'suffix' => date('-dmyhis'), 'field' => 'order_reference']);
        });
    }
}
