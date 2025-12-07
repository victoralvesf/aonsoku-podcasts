<?php

namespace App\Models;

use App\Observers\AdminObserver;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

#[ObservedBy([AdminObserver::class])]
class Admin extends Authenticable implements FilamentUser
{
    use HasFactory, Notifiable, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Get the total count of admins with caching.
     */
    public static function getCount(): int
    {
        $cacheKey = config('constants.cache.count.admin');
        $tts      = config('constants.ONE_DAY_IN_SECONDS');

        return Cache::remember($cacheKey, $tts, fn () => self::count());
    }

    public static function clearGetCountCache(): void
    {
        $cacheKey = config('constants.cache.count.admin');
        Cache::forget($cacheKey);
    }
}
