<?php

namespace App\Jobs;

use App\Helpers\PodcastItemHelper;
use App\Models\Podcast;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use willvincent\Feeds\Facades\FeedsFacade;

class ProcessPodcast implements ShouldQueue
{
    use Queueable, Dispatchable;

    protected User $user;
    protected string $feed_url;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param string $feed_url
     */
    public function __construct(User $user, string $feed_url)
    {
        $this->user = $user;
        $this->feed_url = $feed_url;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $feed = FeedsFacade::make($this->feed_url);

            if ($feed->error !== null) {
                throw new RuntimeException($feed->error);
            }

            $formatted_podcast = PodcastItemHelper::formatPodcast($feed, $this->feed_url);
            $podcast = Podcast::create($formatted_podcast);
            $this->user->podcasts()->attach($podcast->id);

            ProcessPodcastEpisodes::dispatch($podcast);
        } catch (\Exception $e) {
            Log::error('[ProcessPodcastJob] - Error reading the feed.', [
                'feed_url' => $this->feed_url,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
