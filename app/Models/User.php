<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

class User extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'username',
        'tenant_id',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function podcasts(): BelongsToMany
    {
        return $this->belongsToMany(Podcast::class, 'user_podcast');
    }

    /**
     * Get the total count of users with caching.
     */
    public static function getCount(): int
    {
        $cacheKey = config('constants.cache.count.user');
        $tts      = config('constants.ONE_DAY_IN_SECONDS');

        return Cache::remember($cacheKey, $tts, fn () => self::count());
    }
}
