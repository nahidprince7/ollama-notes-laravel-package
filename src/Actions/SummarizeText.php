<?php

namespace Nahid\AINotesPackage\Actions;

use Nahid\AINotesPackage\Contracts\AIProvider;

class SummarizeText
{
    public function __construct(protected AIProvider $provider) {}

    public function handle(string $text): string
    {
        if (strlen($text) < 100) {
            return $text; // Too short to summarize
        }

        return $this->provider->summarize($text);
    }
}