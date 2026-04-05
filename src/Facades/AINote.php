<?php

namespace Nahid\AINotesPackage\Facades;

use Illuminate\Support\Facades\Facade;
use Nahid\AINotesPackage\AINotesManager;

/**
 * @method static \Nahid\AINotesPackage\Models\AINote fromAudio(\Illuminate\Http\UploadedFile $file, ?int $userId = null)
 * @method static \Nahid\AINotesPackage\Models\AINote fromText(string $text, ?int $userId = null, ?string $title = null)
 * @method static \Illuminate\Database\Eloquent\Collection search(string $query, int $limit = 5, ?int $userId = null)
 * @method static string ask(string $question, int $contextLimit = 3, ?int $userId = null)
 */
class AINote extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AINotesManager::class;
    }
}