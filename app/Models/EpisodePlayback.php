<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EpisodePlayback extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'episode_id',
        'progress',
        'completed',
    ];

    protected $casts = [
        'completed' => 'boolean',
    ];

    protected $hidden = [
        'id',
        'user_id',
        'episode_id',
        'created_at',
        'updated_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }
}
