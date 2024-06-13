<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use sirajcse\UniqueIdGenerator\UniqueIdGenerator;

class Customer extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['customer_reference', 'name', 'email', 'address', 'phone', 'is_active'];
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->customer_reference = UniqueIdGenerator::generate(['table' => $model->getTable(), 'length' => 10, 'prefix' => 'CUS-', 'suffix' => date('-dmyhis'), 'field' => 'customer_reference']);
        });
    }
}
