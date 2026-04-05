<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Driver  —  "ollama" (free/local) or "openai" (paid)
    |--------------------------------------------------------------------------
    */
    'driver' => env('AI_DRIVER', 'ollama'),

    /*
    |--------------------------------------------------------------------------
    | Queue Processing
    |--------------------------------------------------------------------------
    | Set false for sync processing (useful in tests / local dev)
    */
    'queue' => env('AI_NOTES_QUEUE', true),

    'ollama' => [
        'base_url'    => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
        'chat_model'  => env('OLLAMA_CHAT_MODEL', 'llama3.2'),
        'embed_model' => env('OLLAMA_EMBED_MODEL', 'nomic-embed-text'),
    ],

    'whisper' => [
        'base_url' => env('WHISPER_BASE_URL', 'http://localhost:9000'),
    ],

    'openai' => [
        'api_key'     => env('OPENAI_API_KEY'),
        'chat_model'  => env('OPENAI_CHAT_MODEL', 'gpt-4o-mini'),
        'embed_model' => env('OPENAI_EMBED_MODEL', 'text-embedding-3-small'),
    ],

    'database' => [
        // 768 = nomic-embed-text (Ollama)
        // 1536 = text-embedding-3-small (OpenAI)
        'vector_dimension' => env('AI_NOTES_VECTOR_DIM', 768),
    ],
];