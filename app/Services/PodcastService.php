<?php

namespace App\Services;

use App\Helpers\FilterHelper;
use App\Helpers\FilterType;
use App\Helpers\PodcastItemHelper;
use App\Jobs\ProcessPodcast;
use App\Jobs\ProcessPodcastEpisodes;
use App\Models\Podcast;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use willvincent\Feeds\Facades\FeedsFacade;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PodcastService
{
    public function getPodcasts(User $user, array $filters)
    {
        $filters = new FilterHelper($filters, FilterType::Podcast);

        return $user->podcasts()
            ->where('is_visible', true)
            ->orderBy($filters->getOrderBy(), $filters->getSort())
            ->simplePaginate($filters->getPerPage());
    }

    public function getPodcastWithEpisodes(User $user, string $podcastId, array $filters)
    {
        $userFollowsThePodcast = $user->podcasts()->where('podcast_id', $podcastId)->exists();

        if (!$userFollowsThePodcast) {
            throw new NotFoundHttpException("Podcast #{$podcastId} not found for this user");
        }

        $podcast = Podcast::where('id', $podcastId)
            ->where('is_visible', true)
            ->first();

        if (!$podcast) {
            throw new NotFoundHttpException("Podcast #{$podcastId} not found");
        }

        $filters = new FilterHelper($filters);

        $episodes = $podcast->episodes()
            ->with(['playback' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->orderBy($filters->getOrderBy(), $filters->getSort())
            ->simplePaginate($filters->getPerPage());

        return [
            'podcast' => $podcast,
            'episodes' => $episodes,
        ];
    }

    public function storePodcast(User $user, string $feed_url)
    {
        $podcast = Podcast::where('feed_url', $feed_url)->first();

        if ($podcast) {
            $userFollowsThePodcast = $user->podcasts()->where('podcast_id', $podcast->id)->exists();

            if (!$userFollowsThePodcast) {
                $user->podcasts()->attach($podcast->id);
            }

            return $podcast;
        }

        try {
            $feed = FeedsFacade::make($feed_url);

            if ($feed->error !== null) {
                throw new RuntimeException($feed->error);
            }

            $formatted_podcast = PodcastItemHelper::formatPodcast($feed, $feed_url);

            $podcast = Podcast::create($formatted_podcast);
            $podcast->refresh();

            $user->podcasts()->attach($podcast->id);

            ProcessPodcastEpisodes::dispatch($podcast);

            return $podcast;
        } catch (\Exception $e) {
            Log::error('[PodcastService] - Error reading the feed.', [
                'feed_url' => $feed_url,
                'message' => $e->getMessage(),
            ]);
            throw new UnprocessableEntityHttpException('Error reading the feed. Please check the URL and try again.');
        }
    }

    public function storePodcastInBackground(User $user, string $feed_url)
    {
        $podcast = Podcast::where('feed_url', $feed_url)->first();

        if (!$podcast) {
            ProcessPodcast::dispatch($user, $feed_url);
        }
    }

    public function destroyPodcast(User $user, string $podcastId)
    {
        $podcastIsLinked = $user->podcasts()->where('podcast_id', $podcastId)->exists();

        if (!$podcastIsLinked) {
            throw new UnprocessableEntityHttpException("The podcast #{$podcastId} is not associated with this user");
        }

        $user->podcasts()->detach($podcastId);
    }

    public function search(User $user, array $filters)
    {
        $filters = new FilterHelper($filters);

        $podcasts = $user->podcasts()
            ->where(function ($query) use ($filters) {
                $searchQuery = $filters->getSearchQuery();
                $filterBy = $filters->getFilterBy();

                switch ($filterBy) {
                    case 'title':
                        $query->where('title', 'like', $searchQuery);
                        break;
                    case 'description':
                        $query->where('description', 'like', $searchQuery);
                        break;
                    default:
                        $query->where('title', 'like', $searchQuery)
                            ->orWhere('description', 'like', $searchQuery);
                        break;
                }
            })
            ->simplePaginate($filters->getPerPage());

        return $podcasts;
    }
}
