<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use sirajcse\UniqueIdGenerator\UniqueIdGenerator;

class Supplier extends Model
{
    use HasFactory;

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->supplier_reference = UniqueIdGenerator::generate(['table' => $model->getTable(), 'length' => 10, 'prefix' => 'SUP-', 'suffix' => date('-dmyhis'), 'field' => 'supplier_reference']);
        });
    }
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}
