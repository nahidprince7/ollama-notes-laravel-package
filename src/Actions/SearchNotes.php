<?php

namespace Nahid\AINotesPackage\Actions;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Nahid\AINotesPackage\Models\AINote;

class SearchNotes
{
    public function __construct(protected GenerateEmbedding $embedder) {}

    public function handle(string $query, int $limit = 5, ?int $userId = null): Collection
    {
        $embedding = $this->embedder->handle($query);
        $vectorLiteral = '[' . implode(',', $embedding) . ']';

        $dbQuery = AINote::query()
            ->where('status', 'done')
            ->whereNotNull('embedding')
            ->selectRaw('*, embedding <=> ?::vector AS distance', [$vectorLiteral])
            ->orderBy('distance')
            ->limit($limit);

        if ($userId) {
            $dbQuery->where('user_id', $userId);
        }

        return $dbQuery->get();
    }
}