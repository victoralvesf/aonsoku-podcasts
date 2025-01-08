<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Podcast extends Model
{
    use HasFactory;

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
}
