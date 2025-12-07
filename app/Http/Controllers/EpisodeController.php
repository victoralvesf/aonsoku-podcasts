<?php

namespace App\Http\Controllers;

use App\Http\Requests\Episode\SearchFilterRequest;
use App\Services\EpisodeService;
use Illuminate\Http\Request;


/**
 * @group Episodes
 */
class EpisodeController extends Controller
{
    protected $episodeService;

    public function __construct(EpisodeService $episodeService)
    {
        $this->episodeService = $episodeService;
    }

    /**
     * @subgroup search
     * @subgroupDescription This section allows users to search for episodes within a specific podcast by providing the podcast ID.
     *
     * @urlParam id string required The unique identifier of the podcast used to search for episodes. Example: 1
     * @queryParam query string required Specifies the search query string to find episodes.
     * @queryParam per_page number Number of items to display per page. Example: 20
     * @queryParam order_by string Specify the order in which the results should be returned. Example: published_at
     * @queryParam sort string Sort order for the results. Example: asc
     * @queryParam filter_by string Specifies the criteria to filter the search results. Example: title
     * @queryParam page number Page number to display. Example: 1
     *
     * @response 200 {
     *   "current_page": 1,
     *   "data": [
     *     {
     *       "id": "0194ab02-5c77-7f2f-b377-1ca5627655c8",
     *       "podcast_id": "0194ab02-9b81-7c1f-a42d-9eba2f7452ea",
     *       "title": "TechTalks 61 - New Year Goals and CES 2025 Highlights",
     *       "description": "In the LAST TECHTALKS EPISODE OF 2024...",
     *       "audio_url": "https://example.com/audio.mp3",
     *       "image_url": "https://example.com/image.jpg",
     *       "duration": 4246,
     *       "published_at": "2024-12-18 18:58:00",
     *       "created_at": "2025-01-05T04:07:37.000000Z",
     *       "updated_at": "2025-01-05T04:07:37.000000Z",
     *       "playback": []
     *     }
     *   ],
     *   "first_page_url": "http://localhost:8000/api/episodes/podcast/1/search?page=1",
     *   "from": 1,
     *   "next_page_url": "",
     *   "path": "http://localhost:8000/api/episodes/podcast/1/search",
     *   "per_page": 20,
     *   "prev_page_url": "",
     *   "to": 1
     * }
     * @response 404 {
     *   "message": "Podcast #0194ab01-792d-7744-b960-5f334e384d13 not found"
     * }
     */
    public function search(SearchFilterRequest $request, string $podcastId)
    {
        $user = $request->user;
        $filters = $request->validated();

        $episodes = $this->episodeService->searchPodcastEpisodes($user, $podcastId, $filters);

        return response()->json($episodes, 200);
    }

    /**
     * @subgroup show
     * @subgroupDescription This section allows users to access episode information.
     *
     * @urlParam id string required Unique identifier for the episode.
     *
     * @response 200 {
     *   "id": "0194aaaf-b71b-7518-ae55-4a88f70c3369",
     *   "podcast_id": "0194aab5-6241-7752-88e3-300d174086a1",
     *   "title": "TechTalks 61 - New Year Goals and CES 2025 Highlights",
     *   "description": "In the LAST TECHTALKS EPISODE OF 2024...",
     *   "audio_url": "https://example.com/audio.mp3",
     *   "image_url": "https://example.com/image.jpg",
     *   "duration": 4246,
     *   "published_at": "2024-12-18 18:58:00",
     *   "created_at": "2025-01-05T04:07:37.000000Z",
     *   "updated_at": "2025-01-05T04:07:37.000000Z",
     *   "playback": []
     * }
     * @response 404 {
     *   "message": "Episode #0194aad3-76fa-7054-ad2a-a77cf9123d6e not found."
     * }
     */
    public function show(Request $request, string $episodeId)
    {
        $user = $request->user;

        $episode = $this->episodeService->getEpisode($user, $episodeId);

        return response()->json($episode, 200);
    }

    /**
     * @subgroup latest
     * @subgroupDescription This section allows users to access the most recent episodes from followed podcasts.
     *
     * @response 200 {
     *   "0": {
     *     "id": "63d8716e-125b-4f3a-8d1c-2547633d51ad",
     *     "podcast_id": "0194aacc-382c-7aea-bdc9-38004300b134",
     *     "title": "TechTalks 61 - New Year Goals and CES 2025 Highlights",
     *     "description": "In the LAST TECHTALKS EPISODE OF 2024...",
     *     "audio_url": "https://example.com/audio.mp3",
     *     "image_url": "https://example.com/image.jpg",
     *     "duration": 4246,
     *     "published_at": "2024-12-18 18:58:00",
     *     "created_at": "2025-01-05T04:07:37.000000Z",
     *     "updated_at": "2025-01-05T04:07:37.000000Z",
     *     "playback": []
     *   }
     * }
     */
    public function latest(Request $request)
    {
        $user = $request->user;

        $episodes = $this->episodeService->getLatestEpisodes($user);

        return response()->json($episodes, 200);
    }

    /**
     * @subgroup progress
     * @subgroupDescription This section allows users to update the progress of a specific podcast episode, enabling them to track their listening progress and easily resume playback from where they left off.
     *
     * @urlParam id string required Unique identifier for the specific episode being updated. Example: 61
     * @bodyParam progress number Value in seconds used to update the progress of the episode. Example: 4246
     *
     * @response 200 {
     *   "progress": 4246,
     *   "completed": false
     * }
     * @response 404 {
     *   "message": "Episode #0194aad3-76fa-7054-ad2a-a77cf9123d6e not found."
     * }
     * @response 422 {
     *   "message": "The progress field is required.",
     *   "errors": {
     *     "progress": [
     *       "The progress field is required."
     *     ]
     *   }
     * }
     */
    public function progress(Request $request, string $episodeId)
    {
        $validated = $request->validate([
            'progress' => 'required|integer|min:0',
        ]);

        $user = $request->user;
        $progress = $validated['progress'];

        $playback = $this->episodeService->updateProgress($user, $episodeId, $progress);

        return response()->json($playback, 200);
    }
}
