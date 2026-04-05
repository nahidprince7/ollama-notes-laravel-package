<?php

namespace Nahid\AINotesPackage\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Nahid\AINotesPackage\Actions\GenerateEmbedding;
use Nahid\AINotesPackage\Actions\SummarizeText;
use Nahid\AINotesPackage\Actions\TranscribeAudio;
use Nahid\AINotesPackage\Contracts\AIProvider;
use Nahid\AINotesPackage\Models\AINote;

class ProcessNote implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 600; // 10 min (Whisper can be slow)

    public function __construct(protected AINote $note) {}

    public function handle(AIProvider $provider): void
    {
        $this->note->update(['status' => 'processing']);

        try {
            $transcription = $this->note->transcription;

            // Step 1: Transcribe if audio
            if ($this->note->source === 'audio' && $this->note->audio_path) {
                $transcriber   = new TranscribeAudio($provider);
                $transcription = $transcriber->handle(
                    storage_path('app/' . $this->note->audio_path)
                );
            }

            // Step 2: Summarize
            $summary = (new SummarizeText($provider))->handle($transcription);

            // Step 3: Embed
            $embedding      = (new GenerateEmbedding($provider))->handle($transcription);
            $vectorLiteral  = '[' . implode(',', $embedding) . ']';

            // Step 4: Save
            $this->note->update([
                'transcription' => $transcription,
                'summary'       => $summary,
                'status'        => 'done',
            ]);

            // pgvector requires raw SQL for the vector cast
            DB::statement(
                'UPDATE ai_notes SET embedding = ?::vector WHERE id = ?',
                [$vectorLiteral, $this->note->id]
            );

        } catch (\Throwable $e) {
            Log::error('ProcessNote failed', [
                'note_id' => $this->note->id,
                'error'   => $e->getMessage(),
            ]);

            $this->note->update(['status' => 'failed']);
            $this->fail($e);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessNote permanently failed', [
            'note_id' => $this->note->id,
            'error'   => $exception->getMessage(),
        ]);
    }
}