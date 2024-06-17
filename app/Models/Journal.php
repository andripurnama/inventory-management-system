<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use sirajcse\UniqueIdGenerator\UniqueIdGenerator;

class Journal extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['reference', 'remarks', 'date'];
    public function journalDetails(): HasMany
    {
        return $this->hasMany(JournalDetail::class);
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->reference = UniqueIdGenerator::generate(['table' => $model->getTable(), 'length' => 10, 'prefix' => 'GR-', 'suffix' => date('-dmyhis'), 'field' => 'reference']);
        });
    }
}
