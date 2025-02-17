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

    protected $podcast;

    /**
     * Create a new job instance.
     *
     * @param Podcast $podcast
     */
    public function __construct(Podcast $podcast)
    {
        $this->podcast = $podcast;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $feed = FeedsFacade::make($this->podcast->feed_url);
            $items = $feed->get_items();
            $consecutiveExistingCount = 0;

            foreach ($items as $item) {
                try {
                    $publishedAt = PodcastItemHelper::getPublishDate($item);

                    $episodeExists = Episode::where('podcast_id', $this->podcast->id)
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

                    $episode = PodcastItemHelper::formatEpisode($item, $this->podcast);
                    Episode::create($episode);
                } catch (\Exception $e) {
                    Log::error("[UpdatePodcastEpisodes] - Error processing episode:", [
                        'podcast' => $this->podcast->title,
                        'episode' => $item->get_title(),
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("[UpdatePodcastEpisodes] - Error updating episodes for podcast:", [
                'id' => $this->podcast->id,
                'title' => $this->podcast->title,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
