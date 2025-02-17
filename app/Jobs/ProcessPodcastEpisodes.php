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

            $reversedItems = array_reverse($feed->get_items());

            foreach ($reversedItems as $item) {
                try {
                    $item_audio_url = PodcastItemHelper::getAudioUrl($item);
                    $audioUrlExists = Episode::where('audio_url', $item_audio_url)->exists();

                    if ($audioUrlExists) {
                        continue;
                    }

                    $episode = PodcastItemHelper::formatEpisode($item, $this->podcast);

                    Episode::create($episode);
                } catch (\Exception $e) {
                    Log::error('Error processing episode for podcast', [
                        'podcast_id' => $this->podcast->id,
                        'episode_title' => $item->get_title(),
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Podcast::where('id', $this->podcast->id)->update(['is_visible' => true]);
        } catch (\Exception $e) {
            Log::error('Error processing job processing for podcast episodes', [
                'id' => $this->podcast->id,
                'title' => $this->podcast->title,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
