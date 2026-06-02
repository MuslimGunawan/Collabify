<?php

use App\Models\Document;
use App\Models\User;
use App\Events\DocumentUpdated;
use App\Events\DocumentDeleted;
use Illuminate\Support\Facades\Event;

test('authenticated user can rename a document', function () {
    Event::fake();

    $user = User::factory()->create();
    $document = Document::create([
        'title' => 'Original Title',
        'content' => 'Some content',
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->put(route('documents.update', $document->id), [
        'title' => 'New Title',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('documents', [
        'id' => $document->id,
        'title' => 'New Title',
    ]);

    Event::assertDispatched(DocumentUpdated::class, function ($event) use ($document) {
        return $event->document->id === $document->id && $event->document->title === 'New Title';
    });
});

test('authenticated user can delete a document', function () {
    Event::fake();

    $user = User::factory()->create();
    $document = Document::create([
        'title' => 'To Be Deleted',
        'content' => 'Some content',
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->delete(route('documents.destroy', $document->id));

    $response->assertRedirect(route('home'));
    $this->assertDatabaseMissing('documents', [
        'id' => $document->id,
    ]);

    Event::assertDispatched(DocumentDeleted::class, function ($event) use ($document) {
        return $event->documentId === $document->id;
    });
});

