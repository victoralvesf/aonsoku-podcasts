<?php

namespace App\Http\Controllers;

use App\Http\Requests\Episode\SearchFilterRequest;
use App\Services\EpisodeService;

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
}
