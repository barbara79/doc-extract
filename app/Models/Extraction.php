<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Extraction extends Model
{
    protected $fillable = [
        'document_id', 'raw_response', 'extracted_fields',
        'confidence_score', 'input_tokens', 'output_tokens', 'cost_usd', 'latency_ms',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'extracted_fields' => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
