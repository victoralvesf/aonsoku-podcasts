<?php

namespace App\Services;

use App\Helpers\FilterHelper;
use App\Models\Episode;
use App\Models\EpisodePlayback;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EpisodeService
{
    public function getEpisode(User $user, string $episodeId)
    {
        $episode = Episode::where('id', $episodeId)
            ->with(['playback' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->first();

        if (!$episode) {
            throw new NotFoundHttpException("Episode #{$episodeId} not found");
        }

        $podcast = $user->podcasts()
            ->where('podcast_id', $episode->podcast_id)
            ->where('is_visible', true)
            ->first();

        if (!$podcast) {
            throw new NotFoundHttpException("Episode #{$episodeId} not found");
        }

        return $episode;
    }

    public function searchPodcastEpisodes(User $user, string $podcastId, array $filters)
    {
        $podcast = $user->podcasts()
            ->where('podcast_id', $podcastId)
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
            ->orderBy($filters->getOrderBy(), $filters->getSort())
            ->simplePaginate($filters->getPerPage());

        return $episodes;
    }

    public function getLatestEpisodes(User $user)
    {
        $followedPodcastIds = DB::table('user_podcast')
            ->select('podcast_id')
            ->where('user_id', $user->id);

        $latestEpisodes = Episode::whereIn('podcast_id', $followedPodcastIds)
            ->with(['playback' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->orderBy('published_at', 'desc')
            ->take(50)
            ->get();

        return $latestEpisodes;
    }

    public function updateProgress(User $user, string $episodeId, int $progress)
    {
        $episode = Episode::where('id', $episodeId)
            ->whereHas('podcast', function ($query) use ($user) {
                $query->whereHas('users', function ($userQuery) use ($user) {
                    $userQuery->where('user_id', $user->id)->where('is_visible', true);
                });
            })->first();

        if (!$episode) {
            throw new NotFoundHttpException("Episode #{$episodeId} not found.");
        }

        $query = ['user_id' => $user->id, 'episode_id' => $episodeId];
        $payload = ['progress' => $progress, 'completed' => $progress >= $episode->duration];

        $playback = EpisodePlayback::updateOrCreate($query, $payload);

        return $playback;
    }
}
