<?php

namespace Nahid\AINotesPackage;

use Illuminate\Http\UploadedFile;
use Nahid\AINotesPackage\Actions\SearchNotes;
use Nahid\AINotesPackage\Contracts\AIProvider;
use Nahid\AINotesPackage\Jobs\ProcessNote;
use Nahid\AINotesPackage\Models\AINote;

class AINotesManager
{
    public function __construct(
        protected AIProvider $provider,
        protected SearchNotes $searcher
    ) {}

    public function fromAudio(UploadedFile $file, ?int $userId = null): AINote
    {
        $path = $file->store('ai-notes/audio', 'local');

        $note = AINote::create([
            'user_id'    => $userId,
            'audio_path' => $path,
            'source'     => 'audio',
            'status'     => 'pending',
        ]);

        $this->dispatch($note);
        return $note;
    }

    public function fromText(string $text, ?int $userId = null, ?string $title = null): AINote
    {
        $note = AINote::create([
            'user_id'       => $userId,
            'title'         => $title,
            'transcription' => $text,
            'source'        => 'text',
            'status'        => 'pending',
        ]);

        $this->dispatch($note);
        return $note;
    }

    public function search(string $query, int $limit = 5, ?int $userId = null)
    {
        return $this->searcher->handle($query, $limit, $userId);
    }

    public function ask(string $question, int $contextLimit = 3, ?int $userId = null): string
    {
        $notes = $this->search($question, $contextLimit, $userId);

        if ($notes->isEmpty()) {
            return "I couldn't find any relevant notes about that.";
        }

        $context = $notes->map(function ($note, $i) {
            return 'Note ' . ($i + 1) . ': ' . ($note->summary ?? $note->transcription);
        })->implode("\n\n");

        return $this->provider->summarize(
            "Based on these notes, answer the question.\n\nNotes:\n{$context}\n\nQuestion: {$question}\n\nAnswer:"
        );
    }

    protected function dispatch(AINote $note): void
    {
        if (config('ai-notes.queue', true)) {
            ProcessNote::dispatch($note);
        } else {
            ProcessNote::dispatchSync($note);
        }
    }


}