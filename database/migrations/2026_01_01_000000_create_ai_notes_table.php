<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enable pgvector extension
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');

        Schema::create('ai_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title')->nullable();
            $table->longText('transcription')->nullable();
            $table->text('summary')->nullable();
            $table->string('audio_path')->nullable();
            $table->json('tags')->nullable();
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])
                  ->default('pending');
            $table->string('source')->default('text'); // 'audio' or 'text'
            $table->timestamps();
        });

        // nomic-embed-text = 768 dims | text-embedding-3-small = 1536 dims
        DB::statement('ALTER TABLE ai_notes ADD COLUMN embedding vector(768)');

        // IVFFlat index for fast approximate nearest-neighbor search
        DB::statement(
            'CREATE INDEX ai_notes_embedding_idx ON ai_notes
             USING ivfflat (embedding vector_cosine_ops) WITH (lists = 100)'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_notes');
    }
};