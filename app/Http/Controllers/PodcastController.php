<?php

namespace App\Http\Controllers;

use App\Http\Requests\Podcast\GetAllFilterRequest;
use App\Http\Requests\Podcast\SearchFilterRequest;
use App\Http\Requests\Podcast\ShowFilterRequest;
use App\Services\PodcastService;
use Illuminate\Http\Request;


/**
 * @group Podcasts
 */
class PodcastController extends Controller
{
    protected $podcastService;

    public function __construct(PodcastService $podcastService)
    {
        $this->podcastService = $podcastService;
    }

    /**
     * @subgroup list
     * @subgroupDescription This section allows users to retrieve a list of followed podcasts.
     *
     * @queryParam per_page number Number of items to display per page. Example: 20
     * @queryParam page number Page number to display. Example: 1
     * @queryParam order_by string Specify the order in which the results should be returned. Example: title
     * @queryParam sort string Sort order for the results. Example: asc
     *
     * @response 200 {
     *   "current_page": 1,
     *   "data": [
     *     {
     *       "id": "0194ab0f-beff-7545-ba71-1caf523acbc9",
     *       "title": "BusinessCast",
     *       "description": "Conversations about business strategies, innovation, and leadership with industry experts.",
     *       "author": "Business Insights",
     *       "link": "https://businessinsights.com/businesscast/",
     *       "image_url": "https://businessinsights.com/assets/images/businesscast.jpg",
     *       "feed_url": "https://feeds.businessinsights.com/5678.rss",
     *       "is_visible": false,
     *       "created_at": "2025-01-05T06:26:16.000000Z",
     *       "updated_at": "2025-01-05T06:26:58.000000Z",
     *       "episode_count": 98
     *     }
     *   ],
     *   "first_page_url": "http://localhost:8000/api/podcasts?page=1",
     *   "from": 1,
     *   "next_page_url": "",
     *   "path": "http://localhost:8000/api/podcasts",
     *   "per_page": 20,
     *   "prev_page_url": "",
     *   "to": 13
     * }
     */
    public function index(GetAllFilterRequest $request)
    {
        $user = $request->user;
        $filters = $request->validated();

        $podcasts = $this->podcastService->getPodcasts($user, $filters);

        return response()->json($podcasts, 200);
    }

    /**
     * @subgroup show
     * @subgroupDescription This section allows users to retrieve information about individual followed podcasts. Users can access details such as episode lists, descriptions, and metadata for each podcast.
     *
     * @urlParam id string required Unique identifier for the podcast.
     * @queryParam per_page number Number of items to display per page. Example: 20
     * @queryParam order_by string Specify the order in which the results should be returned. Example: published_at
     * @queryParam sort string Sort order for the results. Example: desc
     * @queryParam page number Page number to display. Example: 1
     *
     * @response 200 {
     *   "podcast": {
     *     "id": "0194ab09-e6a9-7869-878e-d1116dd2d5fa",
     *     "title": "TechTalks",
     *     "description": "Exploring the latest in technology, gadgets, and software trends.",
     *     "author": "Tech World",
     *     "link": "https://techworld.com/",
     *     "image_url": "https://techworld.com/assets/images/techtalks-logo.jpg",
     *     "feed_url": "https://api.techworld.com/techtalks/feed",
     *     "is_visible": false,
     *     "created_at": "2025-01-05T06:26:12.000000Z",
     *     "updated_at": "2025-01-05T06:26:28.000000Z",
     *     "episode_count": 2
     *   },
     *   "episodes": {
     *     "current_page": 1,
     *     "data": [
     *       {
     *         "id": "0194ab0a-6421-72b8-ab82-875702888bee",
     *         "podcast_id": "0194ab09-e6a9-7869-878e-d1116dd2d5fa",
     *         "title": "TechTalks 60 - AI Myths, Gadget Fails & Smart Home Disasters",
     *         "description": "Episode description placeholder...",
     *         "audio_url": "https://example.com/audio.mp3",
     *         "image_url": "https://example.com/image.jpg",
     *         "duration": 3801,
     *         "published_at": "2024-12-11 18:37:00",
     *         "created_at": "2025-01-05T04:07:37.000000Z",
     *         "updated_at": "2025-01-05T04:07:37.000000Z",
     *         "playback": []
     *       }
     *     ],
     *     "first_page_url": "http://localhost:8000/api/podcasts/1?page=1",
     *     "from": 1,
     *     "next_page_url": "http://localhost:8000/api/podcasts/1?page=2",
     *     "path": "http://localhost:8000/api/podcasts/1",
     *     "per_page": 20,
     *     "prev_page_url": "",
     *     "to": 20
     *   }
     * }
     * @response 404 {
     *   "message": "Podcast #133 not found for this user"
     * }
     */
    public function show(ShowFilterRequest $request, $podcastId)
    {
        $user = $request->user;
        $filters = $request->validated();

        $podcast = $this->podcastService->getPodcastWithEpisodes($user, $podcastId, $filters);

        return response()->json($podcast, 200);
    }

    /**
     * @subgroup create many
     * @subgroupDescription This section allows users to create podcasts by providing the podcast feed url. The podcast episodes will be processed in background.
     *
     * @bodyParam feed_urls string[] array with feed URLs to create multiple podcasts. Example: ["http://podcasturl.com/feed.xml"]
     * @bodyParam feed_url string Single feed URL (alternative to feed_urls).
     *
     * @response 201 {
     *   "0": {
     *     "id": "0194ab0d-05e5-7229-b6fa-d4e113eff2ed",
     *     "title": "TechTalks",
     *     "description": "Exploring the latest in technology...",
     *     "author": "Tech World",
     *     "link": "https://techworld.com/",
     *     "image_url": "https://techworld.com/assets/images/techtalks-logo.jpg",
     *     "feed_url": "https://api.techworld.com/techtalks/feed",
     *     "is_visible": false,
     *     "created_at": "2025-01-05T06:26:12.000000Z",
     *     "updated_at": "2025-01-05T06:26:28.000000Z",
     *     "episode_count": 245
     *   }
     * }
     * @response 202 {
     *   "message": "Processing started. They will be available in just a few minutes!"
     * }
     * @response 422 {
     *   "message": "The feed url field must be a valid URL.",
     *   "errors": {
     *     "feed_url": [
     *       "The feed url field must be a valid URL."
     *     ]
     *   }
     * }
     */
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
            foreach ($validated['feed_urls'] as $feedUrl) {
                $this->podcastService->storePodcastInBackground($user, $feedUrl);
            }
            $response = [
                'message' => 'Processing started. They will be available in just a few minutes!'
            ];
            return response()->json($response, 202);
        }
    }

    /**
     * @subgroup search
     * @subgroupDescription This section allows users to search podcasts by entering specific keywords, or other criteria.
     *
     * @queryParam query string required Specifies the search query string to find relevant podcasts.
     * @queryParam per_page number Number of items to display per page. Example: 20
     * @queryParam filter_by string Specifies the criteria to filter the search results. Example: title
     * @queryParam page number Page number to display. Example: 1
     *
     * @response 200 {
     *   "current_page": 1,
     *   "data": [
     *     {
     *       "id": "0194ab1a-ea18-7c0f-a1e8-48cf55ce63fb",
     *       "title": "BusinessCast",
     *       "description": "Conversations about business strategies...",
     *       "author": "Business Insights",
     *       "link": "https://businessinsights.com/businesscast/",
     *       "image_url": "https://businessinsights.com/assets/images/businesscast.jpg",
     *       "feed_url": "https://feeds.businessinsights.com/5678.rss",
     *       "is_visible": false,
     *       "created_at": "2025-01-05T06:26:16.000000Z",
     *       "updated_at": "2025-01-05T06:26:58.000000Z",
     *       "episode_count": 98
     *     }
     *   ],
     *   "first_page_url": "http://localhost:8000/api/podcasts/search?page=1",
     *   "from": 1,
     *   "next_page_url": "",
     *   "path": "http://localhost:8000/api/podcasts/search",
     *   "per_page": 20,
     *   "prev_page_url": "",
     *   "to": 3
     * }
     */
    public function search(SearchFilterRequest $request)
    {
        $user = $request->user;
        $filters = $request->validated();

        $result = $this->podcastService->search($user, $filters);

        return response()->json($result, 200);
    }

    /**
     * @subgroup unfollow
     * @subgroupDescription This section allows users to unfollow podcasts.
     *
     * @urlParam id string required The unique identifier of the podcast that the user wants to unfollow.
     *
     * @response 204 {}
     * @response 404 {
     *   "message": "The podcast #0194ab04-4f79-7f73-9a44-55a5bfab60df is not associated with this user"
     * }
     */
    public function destroy(Request $request, $podcastId)
    {
        $user = $request->user;
        $this->podcastService->destroyPodcast($user, $podcastId);

        return response()->noContent();
    }
}
