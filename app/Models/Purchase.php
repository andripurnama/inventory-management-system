<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use sirajcse\UniqueIdGenerator\UniqueIdGenerator;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = ['supplier_id', 'purchase_reference', 'grand_total', 'discount', 'tax', 'status', 'remarks'];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->purchase_reference = UniqueIdGenerator::generate(['table' => $model->getTable(), 'length' => 10, 'prefix' => 'PO-', 'suffix' => date('-dmyhis'), 'field' => 'purchase_reference']);
        });
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class);
    }
}
