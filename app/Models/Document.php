<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = [
        'user_id', 'original_filename', 'file_path', 'document_type', 'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function extractions(): HasMany
    {
        return $this->hasMany(Extraction::class);
    }

    // Convenience accessor: the most recent extraction attempt for this document
    public function latestExtraction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Extraction::class)->latestOfMany();
    }
}
