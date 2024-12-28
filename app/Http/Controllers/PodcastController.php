<?php

namespace App\Http\Controllers;

use App\Models\Podcast;
use App\Services\PodcastService;
use Illuminate\Http\Request;
use willvincent\Feeds\Facades\FeedsFacade;

class PodcastController extends Controller
{
    protected $podcastService;

    public function __construct(PodcastService $podcastService)
    {
        $this->podcastService = $podcastService;
    }

    public function index(Request $request)
    {
        $user = $request->user;

        $podcasts = $this->podcastService->getPodcasts($user);

        return response()->json($podcasts, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'feed_url' => 'required|url',
        ]);

        try {
            $user = $request->user;
            $podcast = $this->podcastService->storePodcast($user, $validated['feed_url']);

            return response()->json($podcast, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $podcastId)
    {
        $user = $request->user;

        try {
            $this->podcastService->destroyPodcast($user, $podcastId);

            return response()->noContent();
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
