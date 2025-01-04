<?php

namespace App\Services;

use App\Helpers\FilterHelper;
use App\Models\Episode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EpisodeService
{
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
            ->orderBy('published_at', 'desc')
            ->take(50)
            ->get();

        return $latestEpisodes;
    }
}
