<?php

namespace App\Services;

use App\Jobs\ProcessPodcastEpisodes;
use App\Models\Podcast;
use App\Models\User;
use willvincent\Feeds\Facades\FeedsFacade;
use Exception;

class PodcastService
{
    public function getPodcasts(User $user)
    {
        return $user->podcasts()->where('is_visible', true)->get();
    }

    public function storePodcast($user, $feedUrl)
    {
        $podcast = Podcast::where('feed_url', $feedUrl)->first();

        if ($podcast) {
            $podcastIsLinked = $user->podcasts()->where('podcast_id', $podcast->id)->exists();

            if (!$podcastIsLinked) {
                $user->podcasts()->attach($podcast->id);
            }

            return $podcast;
        }

        try {
            $feed = FeedsFacade::make($feedUrl);

            $title = $feed->get_title();
            $description = $feed->get_description();
            $author = $feed->get_author()->name;
            $link = $feed->get_link();
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
        } catch (Exception $e) {
            throw new Exception('Error reading the feed. Please check the URL and try again.');
        }
    }

    public function destroyPodcast($user, $podcastId)
    {
        $podcastIsLinked = $user->podcasts()->where('podcast_id', $podcastId)->exists();

        if (!$podcastIsLinked) {
            throw new Exception("The podcast #{$podcastId} is not associated with this user");
        }

        $user->podcasts()->detach($podcastId);
    }
}
