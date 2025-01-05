<?php

namespace App\Http\Controllers;

use App\Http\Requests\Podcast\GetAllFilterRequest;
use App\Http\Requests\Podcast\SearchFilterRequest;
use App\Http\Requests\Podcast\ShowFilterRequest;
use App\Services\PodcastService;
use Illuminate\Http\Request;

class PodcastController extends Controller
{
    protected $podcastService;

    public function __construct(PodcastService $podcastService)
    {
        $this->podcastService = $podcastService;
    }

    public function index(GetAllFilterRequest $request)
    {
        $user = $request->user;
        $filters = $request->validated();

        $podcasts = $this->podcastService->getPodcasts($user, $filters);

        return response()->json($podcasts, 200);
    }

    public function show(ShowFilterRequest $request, $podcastId)
    {
        $user = $request->user;
        $filters = $request->validated();

        $podcast = $this->podcastService->getPodcastWithEpisodes($user, $podcastId, $filters);

        return response()->json($podcast, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'feed_url' => 'nullable|string|url|required_without:feed_urls',
            'feed_urls' => 'nullable|array|required_without:feed_url',
            'feed_urls.*' => 'url',
        ]);

        $user = $request->user;

        if (!empty($validated['feed_url'])) {
            $podcast = $this->podcastService->storePodcast($user, $validated['feed_url']);
            return response()->json($podcast, 201);
        }

        if (!empty($validated['feed_urls'])) {
            $podcasts = [];
            foreach ($validated['feed_urls'] as $feedUrl) {
                $podcasts[] = $this->podcastService->storePodcast($user, $feedUrl);
            }
            return response()->json($podcasts, 201);
        }
    }

    public function search(SearchFilterRequest $request)
    {
        $user = $request->user;
        $filters = $request->validated();

        $result = $this->podcastService->search($user, $filters);

        return response()->json($result, 200);
    }

    public function destroy(Request $request, $podcastId)
    {
        $user = $request->user;
        $this->podcastService->destroyPodcast($user, $podcastId);

        return response()->noContent();
    }
}
