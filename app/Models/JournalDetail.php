<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalDetail extends Model
{
    use HasFactory;

    protected $fillable = ['journal_id', 'account_id', 'reference', 'reference_type', 'debit', 'credit'];

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }
}
