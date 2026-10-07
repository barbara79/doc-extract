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
        // $prompt = $this->buildPrompt();

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
        
        $this->document->extractions()->create([
            'raw_response' => $response->json(),
            'extracted_fields' => json_decode($rawText, true),
            'input_tokens' => $response->json('usageMetadata.promptTokenCount'),
            'output_tokens' => $response->json('usageMetadata.candidatesTokenCount'),
            'latency_ms' => $latencyMs,
        ]);

        $this->document->update(['status' => 'extracted']);
    }

    private function buildPrompt(): string
    {
        return "Extract structured invoice data as JSON with fields: "
            . "vendor_name, invoice_number, total_amount, due_date, line_items. "
            . "The document may be in Italian or English — extract and return "
            . "the values regardless of the source language, keeping the JSON keys in English.";
    }
}
