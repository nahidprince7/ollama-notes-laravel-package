<?php

namespace Nahid\AINotesPackage\Drivers;

use GuzzleHttp\Client;
use Nahid\AINotesPackage\Contracts\AIProvider;
use RuntimeException;

class OllamaProvider implements AIProvider
{
    protected Client $http;
    protected Client $whisperHttp;

    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => config('ai-notes.ollama.base_url', 'http://localhost:11434'),
            'timeout'  => 120,
        ]);

        $this->whisperHttp = new Client([
            'base_uri' => config('ai-notes.whisper.base_url', 'http://localhost:9000'),
            'timeout'  => 300,
        ]);
    }

    public function transcribe(string $audioPath): string
    {
        $response = $this->whisperHttp->post('/transcribe', [
            'multipart' => [[
                'name'     => 'audio',
                'contents' => fopen($audioPath, 'r'),
                'filename' => basename($audioPath),
            ]],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if (isset($data['error'])) {
            throw new RuntimeException('Whisper error: ' . $data['error']);
        }

        return $data['text'] ?? '';
    }

    public function summarize(string $text): string
    {
        $response = $this->http->post('/api/chat', [
            'json' => [
                'model'  => config('ai-notes.ollama.chat_model', 'llama3.2'),
                'stream' => false,
                'messages' => [
                    [
                        'role'    => 'system',
                        'content' => 'You are a concise note summarizer. Return only the summary, no commentary.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => "Summarize this note in 2-3 sentences:\n\n{$text}",
                    ],
                ],
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        return $data['message']['content'] ?? '';
    }

    public function embed(string $text): array
    {
        $response = $this->http->post('/api/embeddings', [
            'json' => [
                'model'  => config('ai-notes.ollama.embed_model', 'nomic-embed-text'),
                'prompt' => $text,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if (empty($data['embedding'])) {
            throw new RuntimeException('Ollama returned empty embedding.');
        }

        return $data['embedding'];
    }
}