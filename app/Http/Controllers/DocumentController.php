<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    /**
     * Display a listing of the documents.
     */
    public function index(): Response
    {
        $documents = Document::with('user')
            ->orderBy('updated_at', 'desc')
            ->get();

        return Inertia::render('Dashboard', [
            'documents' => $documents,
        ]);
    }

    /**
     * Store a newly created document in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $document = Document::create([
            'title' => 'Untitled Document',
            'content' => '',
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('documents.show', $document->id);
    }

    /**
     * Display the specified document editor.
     */
    public function show(Document $document): Response
    {
        // Load the document creator relation
        $document->load('user');

        return Inertia::render('Document/Editor', [
            'document' => $document,
        ]);
    }

    /**
     * Update the specified document in storage.
     */
    public function update(UpdateDocumentRequest $request, Document $document): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $document->update(array_filter($validated, fn($value) => $value !== null));

        broadcast(new \App\Events\DocumentUpdated($document))->toOthers();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'document' => $document,
            ]);
        }

        return redirect()->back();
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy(Document $document): RedirectResponse
    {
        $documentId = $document->id;
        $document->delete();

        broadcast(new \App\Events\DocumentDeleted($documentId))->toOthers();

        return redirect()->route('home');
    }

}
