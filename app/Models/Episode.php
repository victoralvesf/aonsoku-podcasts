<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Episode extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'podcast_id',
        'title',
        'description',
        'audio_url',
        'image_url',
        'duration',
        'published_at',
    ];

    protected static function booted()
    {
        static::created(function ($episode) {
            $episode->podcast->increment('episode_count');
        });

        static::deleted(function ($episode) {
            $episode->podcast->decrement('episode_count');
        });
    }

    public function podcast(): BelongsTo
    {
        return $this->belongsTo(Podcast::class);
    }

    public function playback(): HasMany
    {
        return $this->hasMany(EpisodePlayback::class);
    }

    /**
     * Get the total count of episodes with caching.
     */
    public static function getCount(): int
    {
        $cacheKey = config('constants.cache.count.episode');
        $tts      = config('constants.ONE_DAY_IN_SECONDS');

        return Cache::remember($cacheKey, $tts, fn () => self::count());
    }
}
