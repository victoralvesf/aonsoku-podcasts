<?php

namespace Tests\Feature;

use App\Jobs\ProcessPodcastEpisodes;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use willvincent\Feeds\Facades\FeedsFacade;

class PodcastTest extends TestCase
{
    use RefreshDatabase;

    protected array $headers;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $username = 'laravel';
        $serverUrl = 'http://laravel.local.host';

        $this->headers = [
            'APP-USERNAME' => $username,
            'APP-SERVER-URL' => $serverUrl,
        ];

        $tenant = Tenant::factory()->create(['server_url' => $serverUrl]);

        $this->user = User::factory()->create([
            'username' => $username,
            'tenant_id' => $tenant->id,
        ]);
    }

    #[Test]
    public function itShouldGetAnEmptyPodcastsArray()
    {
        $response = $this->getJson(
            route('podcasts.index'),
            $this->headers
        );

        $response->assertOk()
            ->assertJson([
                'data' => []
            ])
            ->assertJsonCount(0, 'data');
    }

    #[Test]
    public function itShouldNotGetUnfollowedPodcasts()
    {
        Podcast::factory()->count(3)->create([
            'is_visible' => true
        ]);

        $response = $this->getJson(
            route('podcasts.index'),
            $this->headers
        );

        $response->assertOk()
            ->assertJson([
                'data' => []
            ])
            ->assertJsonCount(0, 'data');
    }

    #[Test]
    public function itShouldGetFollowedPodcasts()
    {
        $podcasts = Podcast::factory()->count(3)->create([
            'is_visible' => true
        ]);
        $this->user->podcasts()->attach($podcasts);

        $response = $this->getJson(
            route('podcasts.index'),
            $this->headers
        );

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonFragment([
                'title' => $podcasts->first()->title,
            ]);
    }

    #[Test]
    public function itShouldAcceptValidPerPageParam()
    {
        $queryParams = ['per_page' => '50'];
        $url = route('podcasts.index', $queryParams);
        $response = $this->getJson($url, $this->headers);

        $response->assertStatus(200);
    }

    #[Test]
    public function itShouldValidatePerPageParamForStringValues()
    {
        $queryParams = ['per_page' => 'test'];
        $url = route('podcasts.index', $queryParams);
        $response = $this->getJson($url, $this->headers);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('per_page');
    }

    #[Test]
    public function itShouldValidatePerPageParamMinValue()
    {
        $queryParams = ['per_page' => '9'];
        $url = route('podcasts.index', $queryParams);
        $response = $this->getJson($url, $this->headers);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('per_page');
    }

    #[Test]
    public function itShouldValidatePerPageParamMinBoundary()
    {
        $queryParams = ['per_page' => '10'];
        $url = route('podcasts.index', $queryParams);
        $response = $this->getJson($url, $this->headers);

        $response->assertOk();
    }

    #[Test]
    public function itShouldValidatePerPageParamMaxValue()
    {
        $queryParams = ['per_page' => '101'];
        $url = route('podcasts.index', $queryParams);
        $response = $this->getJson($url, $this->headers);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('per_page');
    }

    #[Test]
    public function itShouldValidatePerPageParamMaxBoundary()
    {
        $queryParams = ['per_page' => '100'];
        $url = route('podcasts.index', $queryParams);
        $response = $this->getJson($url, $this->headers);

        $response->assertOk();
    }

    #[Test]
    public function itShouldGetFollowedPodcastById()
    {
        $podcast = Podcast::factory()->create([
            'is_visible' => true
        ]);
        Episode::factory()->count(3)->create([
            'podcast_id' => $podcast->id
        ]);
        $this->user->podcasts()->attach($podcast);

        $url = route('podcasts.show', ['id' => $podcast->id]);
        $response = $this->getJson($url, $this->headers);

        $response->assertOk()
            ->assertJson([
                'podcast' => [
                    'id' => $podcast->id,
                    'title' => $podcast->title,
                    'episode_count' => 3,
                ],
            ])
            ->assertJsonCount(3, 'episodes.data');
    }

    #[Test]
    public function itShouldReturnErrorGettingUnfollowedPodcast()
    {
        $podcast = Podcast::factory()->create([
            'is_visible' => true
        ]);

        $url = route('podcasts.show', ['id' => $podcast->id]);
        $response = $this->getJson($url, $this->headers);

        $response->assertStatus(404);
    }

    #[Test]
    public function itShouldReturnErrorGettingInexistentPodcast()
    {
        $url = route('podcasts.show', ['id' => 999]);
        $response = $this->getJson($url, $this->headers);

        $response->assertStatus(404);
    }

    #[Test]
    public function itShouldCreatePodcastFromASingleFeedUrl()
    {
        Bus::fake();

        $mockFeed = Mockery::mock();
        $mockFeed->shouldReceive('get_title')->andReturn('Mock Podcast');
        $mockFeed->shouldReceive('get_description')->andReturn('A sample mock podcast feed');
        $mockFeed->shouldReceive('get_author')->andReturn((object) ['name' => 'John Doe']);
        $mockFeed->shouldReceive('get_link')->andReturn('http://example.com');
        $mockFeed->shouldReceive('get_image_url')->andReturn('http://example.com/image.jpg');

        FeedsFacade::shouldReceive('make')
            ->once()
            ->with('http://newfeed.com/rss')
            ->andReturn($mockFeed);

        $url = route('podcasts.store');
        $body = ['feed_url' => 'http://newfeed.com/rss'];
        $response = $this->postJson($url, $body, $this->headers);

        $response->assertStatus(201);

        $this->assertDatabaseHas(Podcast::class, [
            'title' => 'Mock Podcast',
            'feed_url' => 'http://newfeed.com/rss'
        ]);

        Bus::assertDispatched(ProcessPodcastEpisodes::class);
    }

    #[Test]
    public function itShouldCreatePodcastFromMultipleFeedUrls()
    {
        Bus::fake();

        $mockFeed1 = Mockery::mock();
        $mockFeed1->shouldReceive('get_title')->andReturn('Mock Podcast 1');
        $mockFeed1->shouldReceive('get_description')->andReturn('A sample mock podcast feed 1');
        $mockFeed1->shouldReceive('get_author')->andReturn((object) ['name' => 'Don Juan']);
        $mockFeed1->shouldReceive('get_link')->andReturn('http://exampleone.com');
        $mockFeed1->shouldReceive('get_image_url')->andReturn('http://exampleone.com/image.jpg');

        FeedsFacade::shouldReceive('make')
            ->with('http://one.newfeed.com/rss')
            ->andReturn($mockFeed1);

        $mockFeed2 = Mockery::mock();
        $mockFeed2->shouldReceive('get_title')->andReturn('Mock Podcast 2');
        $mockFeed2->shouldReceive('get_description')->andReturn('A sample mock podcast feed 2');
        $mockFeed2->shouldReceive('get_author')->andReturn((object) ['name' => 'John Doe']);
        $mockFeed2->shouldReceive('get_link')->andReturn('http://exampletwo.com');
        $mockFeed2->shouldReceive('get_image_url')->andReturn('http://exampletwo.com/image.jpg');

        FeedsFacade::shouldReceive('make')
            ->with('http://two.newfeed.com/rss')
            ->andReturn($mockFeed2);

        $url = route('podcasts.store');
        $body = ['feed_urls' => [
            'http://one.newfeed.com/rss',
            'http://two.newfeed.com/rss',
        ]];
        $response = $this->postJson($url, $body, $this->headers);

        $response->assertStatus(201);

        $this->assertDatabaseHas(Podcast::class, [
            'title' => 'Mock Podcast 1',
            'feed_url' => 'http://one.newfeed.com/rss'
        ]);
        $this->assertDatabaseHas(Podcast::class, [
            'title' => 'Mock Podcast 2',
            'feed_url' => 'http://two.newfeed.com/rss'
        ]);

        Bus::assertDispatchedTimes(ProcessPodcastEpisodes::class, 2);
    }

    #[Test]
    public function itShouldNotCreatePodcastWithoutBody()
    {
        $url = route('podcasts.store');
        $body = [];
        $response = $this->postJson($url, $body, $this->headers);

        $response->assertJsonValidationErrorFor('feed_url');
        $response->assertJsonValidationErrorFor('feed_urls');
    }

    #[Test]
    public function itShouldNotCreatePodcastWithInvalidFeedUrl()
    {
        $url = route('podcasts.store');
        $body = ['feed_url' => '123'];
        $response = $this->postJson($url, $body, $this->headers);

        $response->assertJsonValidationErrorFor('feed_url');
    }

    #[Test]
    public function itShouldNotCreatePodcastWithMalformedFeedUrl()
    {
        $url = route('podcasts.store');
        $body = ['feed_url' => 'testpodcast.com/feed'];
        $response = $this->postJson($url, $body, $this->headers);

        $response->assertJsonValidationErrorFor('feed_url');
    }

    #[Test]
    public function itShouldNotCreatePodcastsWithMalformedFeedUrls()
    {
        $url = route('podcasts.store');
        $body = ['feed_urls' => [
            'testpodcast.com/feed',
            'http://test.com/feed',
        ]];
        $response = $this->postJson($url, $body, $this->headers);

        $response->assertJsonValidationErrorFor('feed_urls.0');
    }

    #[Test]
    public function itShouldSearchPodcastsByTitleAndDescription()
    {
        $query = fake()->sentence(4);
        $podcast1 = Podcast::factory()->create([
            'title' => $query,
            'is_visible' => true
        ]);
        $podcast2 = Podcast::factory()->create([
            'description' => $query,
            'is_visible' => true
        ]);
        $this->user->podcasts()->attach([$podcast1, $podcast2]);

        $queryParams = [
            'query' => $query,
            'filter_by' => 'both'
        ];
        $url = route('podcasts.search', $queryParams);
        $response = $this->getJson($url, $this->headers);

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'title' => $podcast1->title,
            ])
            ->assertJsonFragment([
                'title' => $podcast2->title,
            ]);
    }

    #[Test]
    public function itShouldNotSearchPodcastsWithoutQuery()
    {
        $url = route('podcasts.search');
        $response = $this->getJson($url, $this->headers);

        $response->assertJsonValidationErrorFor('query');
    }

    #[Test]
    public function itShouldNotSearchPodcastsWithInvalidQuery()
    {
        $queryParams = ['query' => 'ab'];
        $url = route('podcasts.search', $queryParams);
        $response = $this->getJson($url, $this->headers);

        $response->assertJsonValidationErrorFor('query');
    }

    #[Test]
    public function itShouldNotSearchPodcastsWithInvalidFilterBy()
    {
        $queryParams = [
            'query' => 'my podcast',
            'filter_by' => 'invalid'
        ];
        $url = route('podcasts.search', $queryParams);
        $response = $this->getJson($url, $this->headers);

        $response->assertJsonValidationErrorFor('filter_by');
    }

    #[Test]
    public function itShouldUnfollowPodcast()
    {
        $podcast = Podcast::factory()->create([
            'is_visible' => true
        ]);
        $this->user->podcasts()->attach($podcast);

        $url = route('podcasts.destroy', ['id' => $podcast->id]);
        $response = $this->deleteJson($url, [], $this->headers);

        $response->assertStatus(204);
    }

    #[Test]
    public function itShouldFailWhenRemovingUnfollowedPodcast()
    {
        $podcast = Podcast::factory()->create([
            'is_visible' => true
        ]);

        $url = route('podcasts.destroy', ['id' => $podcast->id]);
        $response = $this->deleteJson($url, [], $this->headers);

        $response->assertStatus(422);
    }
}
