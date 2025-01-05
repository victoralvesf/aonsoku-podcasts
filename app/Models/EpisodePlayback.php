<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EpisodePlayback extends Model
{
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function episode()
    {
        return $this->belongsTo(Episode::class);
    }
}
