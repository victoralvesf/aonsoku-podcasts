<?php

namespace App\Jobs;

use App\Helpers\PodcastItemHelper;
use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use willvincent\Feeds\Facades\FeedsFacade;

class UpdatePodcastEpisodes implements ShouldQueue
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
            try {
                $feed = FeedsFacade::make($podcast->feed_url);
                $items = $feed->get_items();
                $consecutiveExistingCount = 0;

                foreach ($items as $item) {
                    try {
                        $publishedAt = PodcastItemHelper::getPublishDate($item);

                        $episodeExists = Episode::where('podcast_id', $podcast->id)
                            ->where('published_at', $publishedAt)
                            ->exists();

                        if ($episodeExists) {
                            $consecutiveExistingCount++;

                            // Stops foreach if find at least 3 existing episodes.
                            if ($consecutiveExistingCount >= 3) {
                                break;
                            }

                            continue;
                        }

                        $consecutiveExistingCount = 0;

                        $episode = PodcastItemHelper::formatEpisode($item, $podcast);
                        Episode::create($episode);
                    } catch (\Exception $e) {
                        Log::error("[UpdatePodcastEpisodes] - Error processing episode:", [
                            'podcast' => $podcast->title,
                            'episode' => $item->get_title(),
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error("[UpdatePodcastEpisodes] - Error updating episodes for podcast:", [
                    'id' => $podcast->id,
                    'title' => $podcast->title,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
