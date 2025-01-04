<?php

namespace App\Http\Controllers;

use App\Http\Requests\Episode\SearchFilterRequest;
use App\Services\EpisodeService;
use Illuminate\Http\Request;

class EpisodeController extends Controller
{
    protected $episodeService;

    public function __construct(EpisodeService $episodeService)
    {
        $this->episodeService = $episodeService;
    }

    public function search(SearchFilterRequest $request, string $podcastId)
    {
        $user = $request->user;
        $filters = $request->validated();

        $episodes = $this->episodeService->searchPodcastEpisodes($user, $podcastId, $filters);

        return response()->json($episodes, 200);
    }

    public function latest(Request $request)
    {
        $user = $request->user;

        $episodes = $this->episodeService->getLatestEpisodes($user);

        return response()->json($episodes, 200);
    }
}
