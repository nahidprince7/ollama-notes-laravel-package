<?php

namespace Nahid\AINotesPackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AINote extends Model
{
    protected $table = 'ai_notes';

    protected $fillable = [
        'user_id', 'title', 'transcription', 'summary',
        'audio_path', 'tags', 'status', 'source',
    ];

    protected $casts = ['tags' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function scopePending($query)  { return $query->where('status', 'pending'); }
    public function scopeDone($query)     { return $query->where('status', 'done'); }
}