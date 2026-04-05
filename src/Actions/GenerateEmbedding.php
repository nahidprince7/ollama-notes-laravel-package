<?php

namespace Nahid\AINotesPackage\Actions;

use Nahid\AINotesPackage\Contracts\AIProvider;

class GenerateEmbedding
{
    public function __construct(protected AIProvider $provider) {}

    /** @return float[] */
    public function handle(string $text): array
    {
        // Truncate to avoid token limits
        $text = mb_substr($text, 0, 8000);

        return $this->provider->embed($text);
    }
}