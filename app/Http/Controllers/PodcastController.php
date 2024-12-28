<?php

namespace App\Http\Controllers;

use App\Models\Podcast;
use Illuminate\Http\Request;
use willvincent\Feeds\Facades\FeedsFacade;

class PodcastController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user;

        $podcasts = $user->podcasts()->get()->where('is_visible', true);

        return response()->json($podcasts, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'feed_url' => 'required|url',
        ]);

        $podcast = Podcast::where('feed_url', $validated['feed_url'])->first();

        if ($podcast) {
            $user = $request->user;
            $podcastIsAlreadyLinked = $user->podcasts()->where('podcast_id', $podcast->id)->exists();

            if (!$podcastIsAlreadyLinked) {
                $user->podcasts()->attach($podcast->id);
            }

            return response()->json($podcast, 201);
        }

        try {
            $feed = FeedsFacade::make($validated['feed_url']);

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
                'feed_url' => $validated['feed_url'],
            ]);

            $podcast->refresh();

            $user = $request->user;
            $user->podcasts()->attach($podcast->id);

            return response()->json($podcast, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao ler o feed. Verifique a URL e tente novamente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $podcastId)
    {
        $user = $request->user;

        $podcastIsLinked = $user->podcasts()->where('podcast_id', $podcastId)->exists();
        if (!$podcastIsLinked) {
            return response()->json([
                'message' => "The podcast #{$podcastId} is not associated with this user",
            ], 404);
        }

        $user->podcasts()->detach($podcastId);

        return response()->noContent();
    }
}
