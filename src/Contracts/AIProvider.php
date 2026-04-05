<?php

namespace Nahid\AINotesPackage\Contracts;

interface AIProvider
{
    /** Transcribe audio file path → text */
    public function transcribe(string $audioPath): string;

    /** Summarize long text → short summary */
    public function summarize(string $text): string;

    /** Generate float[] embedding vector for text */
    public function embed(string $text): array;
}