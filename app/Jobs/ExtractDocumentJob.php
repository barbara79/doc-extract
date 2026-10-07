<?php

namespace App\Jobs;

use App\Models\Document;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExtractDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Document $document)
    {
        
    }

    public function handle(): void
    {
        $start = microtime(true);
        $fileContent = \Illuminate\Support\Facades\Storage::disk('local')
            ->get($this->document->file_path);
        $base64File = base64_encode($fileContent);

        $response = Http::post(
            'https://generativelanguage.googleapis.com/v1beta/models/'
            . config('services.gemini.model')
            . ':generateContent?key=' . config('services.gemini.key'),
            [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'inlineData' => [
                                    'mimeType' => 'application/pdf',
                                    'data' => $base64File,
                                ],
                            ],
                            ['text' => $this->buildPrompt()],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                ],
            ]
        );

        $latencyMs = (int)(microtime(true) - $start) * 1000; 
        $rawText = $response->json('candidates.0.content.parts.0.text');


        $parsed = json_decode($rawText, true);
        $confidence = $parsed['confidence']['overall'] ?? 0;
        
        $this->document->extractions()->create([
            'raw_response' => $response->json(),
            'extracted_fields' => json_decode($rawText, true),
            'confidence_score' => $confidence,
            'input_tokens' => $response->json('usageMetadata.promptTokenCount'),
            'output_tokens' => $response->json('usageMetadata.candidatesTokenCount'),
            'latency_ms' => $latencyMs,
        ]);

        $newStatus = $confidence >= 80 ? 'validated' : 'needs_review';
        $this->document->update(['status' => $newStatus]);
    }

    private function buildPrompt(): string
    {
        return <<<PROMPT
            Extract structured invoice data from this document as JSON.

            For each field, also assess your confidence (0-100) based on how
            clearly the value was stated in the document. Use a lower score
            if a value was ambiguous, partially visible, or inferred rather
            than explicitly stated.

            Return exactly this JSON shape:
            {
            "vendor_name": "string or null",
            "invoice_number": "string or null",
            "total_amount": "number or null",
            "due_date": "string (YYYY-MM-DD) or null",
            "line_items": [
                { "description": "string", "quantity": number, "unit_price": number, "total_price": number }
            ],
            "confidence": {
                "overall": number,
                "fields": {
                "vendor_name": number,
                "invoice_number": number,
                "total_amount": number,
                "due_date": number
                }
            }
            }

            The document may be in Italian or English — extract and return
            values regardless of source language, keeping JSON keys in English.
            PROMPT;
    }
}
