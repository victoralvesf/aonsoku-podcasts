<?php

namespace App\Jobs;

use App\Models\Podcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class TriggerPodcastsUpdate implements ShouldQueue
{
    use Dispatchable, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $podcasts = Podcast::cursor();

        foreach ($podcasts as $podcast) {
            Log::info("Dispatching UpdatePodcastEpisodes for podcast:", [
                'id' => $podcast->id,
                'title' => $podcast->title,
            ]);

            UpdatePodcastEpisodes::dispatch($podcast);
        }
    }
}
