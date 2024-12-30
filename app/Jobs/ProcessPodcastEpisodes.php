<?php

namespace App\Jobs;

use App\Helpers\PodcastItemHelper;
use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use willvincent\Feeds\Facades\FeedsFacade;

class ProcessPodcastEpisodes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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

            $reversedItems = array_reverse($feed->get_items());
            $podcastImage = $feed->get_image_url();

            foreach ($reversedItems as $item) {
                try {
                    $audioUrlExists = Episode::where('audio_url', $item->get_permalink())->exists();

                    if ($audioUrlExists) {
                        continue;
                    }

                    Episode::create([
                        'podcast_id' => $this->podcast->id,
                        'title' => $item->get_title(),
                        'description' => $item->get_content(),
                        'audio_url' => $item->get_enclosure()->get_link(),
                        'image_url' => PodcastItemHelper::getItunesImage($item, $podcastImage),
                        'duration' => PodcastItemHelper::getItunesDuration($item),
                        'published_at' => $item->get_date('Y-m-d H:i:s'),
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Error processing episode for podcast', [
                        'podcast_id' => $this->podcast->id,
                        'episode_title' => $item->get_title(),
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::table('podcasts')
                ->where('id', $this->podcast->id)
                ->update(['is_visible' => 1]);
        } catch (\Exception $e) {
            Log::error('Error processing job processing for podcast episodes', [
                'id' => $this->podcast->id,
                'title' => $this->podcast->title,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
