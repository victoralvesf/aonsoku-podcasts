<?php

namespace App\Models;

use App\Observers\PodcastObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

#[ObservedBy([PodcastObserver::class])]
class Podcast extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'description',
        'author',
        'link',
        'image_url',
        'feed_url',
        'is_visible',
        'episode_count',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'episode_count' => 'integer',
    ];

    protected $hidden = ['pivot'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_podcast');
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    /**
     * Get the total count of podcasts with caching.
     */
    public static function getCount(): int
    {
        $cacheKey = config('constants.cache.count.podcast');
        $tts      = config('constants.ONE_DAY_IN_SECONDS');

        return Cache::remember($cacheKey, $tts, fn () => self::count());
    }

    public static function clearGetCountCache(): void
    {
        $cacheKey = config('constants.cache.count.podcast');

        Cache::forget($cacheKey);
    }
}
