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

            foreach ($feed->get_items() as $item) {
                $audioUrlExists = Episode::where('audio_url', $item->get_permalink())->exists();

                if ($audioUrlExists) {
                    continue;
                }

                Episode::create([
                    'podcast_id' => $this->podcast->id,
                    'title' => $item->get_title(),
                    'description' => $item->get_content(),
                    'audio_url' => $item->get_enclosure()->get_link(),
                    'image_url' => PodcastItemHelper::getItunesImage($item),
                    'duration' => PodcastItemHelper::getItunesDuration($item),
                    'published_at' => $item->get_date('Y-m-d H:i:s'),
                ]);
            }

            Podcast::find($this->podcast->id)->update([
                'is_visible' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing episodes for podcast', [
                'id' => $this->podcast->id,
                'title' => $this->podcast->title,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
