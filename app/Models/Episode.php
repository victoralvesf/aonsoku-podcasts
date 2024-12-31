<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Episode extends Model
{
    use HasFactory;

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

    public function podcast()
    {
        return $this->belongsTo(Podcast::class);
    }
}
