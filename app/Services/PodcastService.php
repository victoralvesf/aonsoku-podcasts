<?php

namespace App\Services;

use App\Jobs\ProcessPodcastEpisodes;
use App\Models\Podcast;
use App\Models\User;
use willvincent\Feeds\Facades\FeedsFacade;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PodcastService
{
    public function getPodcasts(User $user)
    {
        return $user->podcasts()->where('is_visible', true)->get();
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

        // Episodes Filters
        $perPage = 20;
        $sort = 'desc';
        $orderBy = 'published_at';

        if (!empty($filters['per_page'])) {
            $perPage = intval($filters['per_page']);
        }

        if (!empty($filters['sort'])) {
            $sort = $filters['sort'];
        }

        if (!empty($filters['order_by'])) {
            $orderBy = $filters['order_by'];
        }

        $episodes = $podcast->episodes()
            ->orderBy($orderBy, $sort)
            ->simplePaginate($perPage);

        return [
            'podcast' => $podcast,
            'episodes' => $episodes,
        ];
    }

    public function storePodcast(User $user, string $feedUrl)
    {
        $podcast = Podcast::where('feed_url', $feedUrl)->first();

        if ($podcast) {
            $userFollowsThePodcast = $user->podcasts()->where('podcast_id', $podcast->id)->exists();

            if (!$userFollowsThePodcast) {
                $user->podcasts()->attach($podcast->id);
            }

            return $podcast;
        }

        try {
            $feed = FeedsFacade::make($feedUrl);

            $title = $feed->get_title();
            $description = $feed->get_description() ?? '';
            $author = $feed->get_author()->name ?? '';
            $link = $feed->get_link() ?? '';
            $image_url = $feed->get_image_url();

            $podcast = Podcast::create([
                'title' => $title,
                'description' => $description,
                'author' => $author,
                'link' => $link,
                'image_url' => $image_url,
                'feed_url' => $feedUrl,
            ]);
            $podcast->refresh();

            $user->podcasts()->attach($podcast->id);

            ProcessPodcastEpisodes::dispatch($podcast);

            return $podcast;
        } catch (\Exception) {
            throw new UnprocessableEntityHttpException('Error reading the feed. Please check the URL and try again.');
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
        $perPage = 20;

        if (!empty($filters['per_page'])) {
            $perPage = $filters['per_page'];
        }

        $searchQuery = "%{$filters['query']}%";

        $podcasts = $user->podcasts()
            ->where(function ($query) use ($searchQuery) {
                $query->where('title', 'like', $searchQuery)
                    ->orWhere('description', 'like', $searchQuery);
            })
            ->simplePaginate($perPage);

        return $podcasts;
    }
}
