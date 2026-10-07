<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Jobs\ExtractDocumentJob;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use App\Policies\DocumentPolicy;

class DocumentController extends Controller
{
    public function index(): Response
    {
        $documents = auth()->user()->documents()->latest()->get();

        return inertia('Documents/Index', 
        [
            'documents' => $documents,
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $file = $request->file('file');
        $path = $file->store('documents');

        $document = auth()->user()->documents()->create([
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'document_type' => $request->input('document_type'),
        ]);

        ExtractDocumentJob::dispatch($document);

        return redirect()->route('documents.index');
    }

    public function show(Document $document): Response
    {
        $this->authorize('view', $document);
        $document->load('latestExtraction');

        return inertia('Documents/Show', [
            'document' => $document,
        ]);
    }
}
