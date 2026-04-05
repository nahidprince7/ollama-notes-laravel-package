<?php

namespace Nahid\AINotesPackage\Drivers;

use GuzzleHttp\Client;
use Nahid\AINotesPackage\Contracts\AIProvider;

class OpenAIProvider implements AIProvider
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => 'https://api.openai.com',
            'timeout'  => 120,
            'headers'  => [
                'Authorization' => 'Bearer ' . config('ai-notes.openai.api_key'),
            ],
        ]);
    }

    public function transcribe(string $audioPath): string
    {
        $response = $this->http->post('/v1/audio/transcriptions', [
            'multipart' => [
                ['name' => 'file',  'contents' => fopen($audioPath, 'r'), 'filename' => basename($audioPath)],
                ['name' => 'model', 'contents' => 'whisper-1'],
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true)['text'] ?? '';
    }

    public function summarize(string $text): string
    {
        $response = $this->http->post('/v1/chat/completions', [
            'json' => [
                'model'    => config('ai-notes.openai.chat_model', 'gpt-4o-mini'),
                'messages' => [
                    ['role' => 'system', 'content' => 'Summarize the note in 2-3 sentences. Return only the summary.'],
                    ['role' => 'user',   'content' => $text],
                ],
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true)['choices'][0]['message']['content'] ?? '';
    }

    public function embed(string $text): array
    {
        $response = $this->http->post('/v1/embeddings', [
            'json' => [
                'model' => config('ai-notes.openai.embed_model', 'text-embedding-3-small'),
                'input' => $text,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true)['data'][0]['embedding'] ?? [];
    }
}