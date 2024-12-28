<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    protected $hidden = ['pivot'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_podcast');
    }

    public function episodes()
    {
        return $this->hasMany(Episode::class)->orderBy('published_at', 'desc');
    }
}
