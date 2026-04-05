<?php

namespace Nahid\AINotesPackage\Actions;

use Nahid\AINotesPackage\Contracts\AIProvider;

class TranscribeAudio
{
    public function __construct(protected AIProvider $provider) {}

    public function handle(string $audioPath): string
    {
        if (! file_exists($audioPath)) {
            throw new \InvalidArgumentException("Audio file not found: {$audioPath}");
        }

        return $this->provider->transcribe($audioPath);
    }
}