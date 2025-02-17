<?php

namespace Tests\Unit;

use App\Jobs\ProcessPodcast;
use App\Jobs\ProcessPodcastEpisodes;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\User;
use App\Services\PodcastService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use SimplePie\SimplePie;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Tests\TestCase;
use willvincent\Feeds\Facades\FeedsFacade;

class PodcastServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PodcastService $podcastService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->podcastService = app(PodcastService::class);
    }

    #[Test]
    public function itShouldReturnAllFollowedPodcasts()
    {
        $user = User::factory()->create();

        $podcast1 = Podcast::factory()->create([
            'title' => 'AB Podcast',
            'is_visible' => true
        ]);
        Podcast::factory()->create(['is_visible' => true]);
        $podcast3 = Podcast::factory()->create([
            'title' => 'BC Podcast',
            'is_visible' => true
        ]);

        $user->podcasts()->attach($podcast1);
        $user->podcasts()->attach($podcast3);

        $podcasts = $this->podcastService->getPodcasts($user, []);

        $this->assertCount(2, $podcasts->items());
        $this->assertEquals($podcast1->id, $podcasts->items()[0]->id);
        $this->assertEquals($podcast3->id, $podcasts->items()[1]->id);
    }

    #[Test]
    public function itShouldReturnAllFollowedPodcastsSortingByDesc()
    {
        $user = User::factory()->create();

        $podcast1 = Podcast::factory()->create([
            'title' => 'AB Podcast',
            'is_visible' => true
        ]);
        Podcast::factory()->create(['is_visible' => true]);
        $podcast3 = Podcast::factory()->create([
            'title' => 'BC Podcast',
            'is_visible' => true
        ]);

        $user->podcasts()->attach($podcast1);
        $user->podcasts()->attach($podcast3);

        $podcasts = $this->podcastService->getPodcasts($user, [
            'sort' => 'desc'
        ]);

        $this->assertCount(2, $podcasts->items());
        $this->assertEquals($podcast3->id, $podcasts->items()[0]->id);
        $this->assertEquals($podcast1->id, $podcasts->items()[1]->id);
    }

    #[Test]
    public function itShouldReturnAllFollowedPodcastsOrderingByEpisodeCount()
    {
        $user = User::factory()->create();

        $podcast1 = Podcast::factory()->create([
            'episode_count' => 100,
            'is_visible' => true
        ]);
        Podcast::factory()->create(['is_visible' => true]);
        $podcast3 = Podcast::factory()->create([
            'episode_count' => 90,
            'is_visible' => true
        ]);

        $user->podcasts()->attach($podcast1);
        $user->podcasts()->attach($podcast3);

        $podcasts = $this->podcastService->getPodcasts($user, [
            'order_by' => 'episode_count'
        ]);

        $this->assertCount(2, $podcasts->items());
        $this->assertEquals($podcast3->id, $podcasts->items()[0]->id);
        $this->assertEquals($podcast1->id, $podcasts->items()[1]->id);
    }

    #[Test]
    public function itShouldReturnAllFollowedPodcastsOrderingByEpisodeCountAndSortDesc()
    {
        $user = User::factory()->create();

        $podcast1 = Podcast::factory()->create([
            'episode_count' => 100,
            'is_visible' => true
        ]);
        Podcast::factory()->create(['is_visible' => true]);
        $podcast3 = Podcast::factory()->create([
            'episode_count' => 90,
            'is_visible' => true
        ]);

        $user->podcasts()->attach($podcast1);
        $user->podcasts()->attach($podcast3);

        $podcasts = $this->podcastService->getPodcasts($user, [
            'order_by' => 'episode_count',
            'sort' => 'desc'
        ]);

        $this->assertCount(2, $podcasts->items());
        $this->assertEquals($podcast1->id, $podcasts->items()[0]->id);
        $this->assertEquals($podcast3->id, $podcasts->items()[1]->id);
    }

    #[Test]
    public function itShouldReturnAPodcastWithEpisodes()
    {
        $user = User::factory()->create();
        $podcast = Podcast::factory()->create(['is_visible' => true]);
        $user->podcasts()->attach($podcast);
        $episode1 = Episode::factory()->create([
            'podcast_id' => $podcast->id,
            'title' => 'EP 01 - Pilot',
            'published_at' => now()->subDays(1),
        ]);
        $episode2 = Episode::factory()->create([
            'podcast_id' => $podcast->id,
            'title' => 'EP 02 - Hello Again',
            'published_at' => now()->subDays(2),
        ]);

        $result = $this->podcastService->getPodcastWithEpisodes($user, $podcast->id, []);

        $resultPodcast = $result['podcast'];
        $resultEpisodes = $result['episodes'];

        $this->assertEquals($podcast->id, $resultPodcast->id);
        $this->assertEquals($podcast->title, $resultPodcast->title);
        $this->assertEquals(2, $resultPodcast->episode_count);

        $this->assertCount(2, $resultEpisodes->items());
        $this->assertEquals($episode1->id, $resultEpisodes->items()[0]->id);
        $this->assertEquals($episode2->id, $resultEpisodes->items()[1]->id);
    }

    #[Test]
    public function itShouldFailToGetAPodcastThatIsNotFollowed()
    {
        $user = User::factory()->create();
        $podcast = Podcast::factory()->create(['is_visible' => true]);

        $this->expectException(NotFoundHttpException::class);
        $this->podcastService->getPodcastWithEpisodes($user, $podcast->id, []);
    }

    #[Test]
    public function itShouldFailToGetAPodcastThatIsNotVisible()
    {
        $user = User::factory()->create();
        $podcast = Podcast::factory()->create(['is_visible' => false]);
        $user->podcasts()->attach($podcast);

        $this->expectException(NotFoundHttpException::class);
        $this->podcastService->getPodcastWithEpisodes($user, $podcast->id, []);
    }

    #[Test]
    public function itShouldReturnExistingPodcastIfFeedUrlAlreadyExists()
    {
        $user = User::factory()->create();
        $feedUrl = fake()->url();
        $podcast = Podcast::factory()->create(['feed_url' => $feedUrl]);

        $userPodcastsBeforeStore = $user->podcasts()->where('podcast_id', $podcast->id)->exists();

        $result = $this->podcastService->storePodcast($user, $feedUrl);

        $userPodcastsAfterStore = $user->podcasts()->where('podcast_id', $podcast->id)->exists();

        $this->assertEquals($podcast->id, $result->id);
        $this->assertFalse($userPodcastsBeforeStore);
        $this->assertTrue($userPodcastsAfterStore);
    }

    #[Test]
    public function itShouldCreatePodcastAndDispatchTheEpisodeProcessJob()
    {
        $user = User::factory()->create();

        $mock_feed = Mockery::mock(SimplePie::class, [
            'get_title' => 'Test Podcast',
            'get_description' => 'A sample podcast feed',
            'get_author' => ['name' => 'John Doe'],
            'get_link' => 'http://example.com',
            'get_image_url' => 'http://example.com/image.jpg',
            'error' => null,
        ]);

        $feed_url = 'http://newfeed.com/rss';

        FeedsFacade::shouldReceive('make')
            ->once()
            ->with($feed_url)
            ->andReturn($mock_feed);

        Bus::fake();

        $podcast = $this->podcastService->storePodcast($user, $feed_url);
        $podcast_is_now_followed = $user->podcasts()->where('podcast_id', $podcast->id)->exists();

        $this->assertTrue($podcast_is_now_followed);

        $this->assertEquals('Test Podcast', $podcast->title);
        $this->assertEquals('A sample podcast feed', $podcast->description);

        Bus::assertDispatched(ProcessPodcastEpisodes::class);
    }

    #[Test]
    public function itShouldNotCreatePodcastIfFeedsReturnsError()
    {
        $user = User::factory()->create();

        $mock_feed = Mockery::mock(SimplePie::class, [
            'get_title' => 'Test Podcast',
            'get_description' => 'A sample podcast feed',
            'get_author' => ['name' => 'John Doe'],
            'get_link' => 'http://example.com',
            'get_image_url' => 'http://example.com/image.jpg',
            'error' => 'Some fake error',
        ]);

        $feed_url = 'http://newfeed.com/rss';

        FeedsFacade::shouldReceive('make')
            ->once()
            ->with($feed_url)
            ->andReturn($mock_feed);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($feed_url) {
                $is_correct_feed = $context['feed_url'] === $feed_url;
                $is_correct_error = $context['message'] === 'Some fake error';

                return $is_correct_feed && $is_correct_error;
            });

        $this->expectException(UnprocessableEntityHttpException::class);

        $this->podcastService->storePodcast($user, $feed_url);
    }

    #[Test]
    public function itShouldThrowExceptionWhenFeedIsInvalid()
    {
        $user = User::factory()->create();

        FeedsFacade::shouldReceive('make')->once()->andThrow(new Exception('Invalid Feed'));

        $this->expectException(UnprocessableEntityHttpException::class);

        $this->podcastService->storePodcast($user, 'http://invalidfeed.web/rss');
    }

    #[Test]
    public function itShouldNotDispatchProcessPodcastJobForExistentFeeds()
    {
        Bus::fake();

        $user = User::factory()->create();
        $feed_url = Fake()->url();
        Podcast::factory()->create([
            'feed_url' => $feed_url,
            'is_visible' => true
        ]);

        $this->podcastService->storePodcastInBackground($user, $feed_url);

        Bus::assertNotDispatched(ProcessPodcast::class);
    }

    #[Test]
    public function itShouldDispatchProcessPodcastJob()
    {
        Bus::fake();

        $user = User::factory()->create();
        $feed_url = Fake()->url();

        $this->podcastService->storePodcastInBackground($user, $feed_url);

        Bus::assertDispatchedTimes(ProcessPodcast::class, 1);
    }

    #[Test]
    public function itShouldNotDispatchProcessPodcastJobForFeedsWithError()
    {
        $user = User::factory()->create();

        $mock_feed = Mockery::mock(SimplePie::class, [
            'get_title' => 'Test Podcast',
            'get_description' => 'A sample podcast feed',
            'get_author' => ['name' => 'John Doe'],
            'get_link' => 'http://example.com',
            'get_image_url' => 'http://example.com/image.jpg',
            'error' => 'Some fake error',
        ]);

        $feed_url = 'http://newfeed.com/rss';

        FeedsFacade::shouldReceive('make')
            ->once()
            ->with($feed_url)
            ->andReturn($mock_feed);

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($feed_url) {
                $is_correct_feed = $context['feed_url'] === $feed_url;
                $is_correct_error = $context['message'] === 'Some fake error';

                return $is_correct_feed && $is_correct_error;
            });

        $this->podcastService->storePodcastInBackground($user, $feed_url);
    }

    #[Test]
    public function itShouldUnfollowThePodcast()
    {
        $user = User::factory()->create();
        $podcast = Podcast::factory()->create(['is_visible' => true]);
        $user->podcasts()->attach($podcast);

        $this->podcastService->destroyPodcast($user, $podcast->id);

        $podcastIsFollowed = $user->podcasts()->where('podcast_id', $podcast->id)->exists();

        $this->assertFalse($podcastIsFollowed);
    }

    #[Test]
    public function itShouldFailWhenAttemptingToUnfollowANonFollowedPodcast()
    {
        $user = User::factory()->create();
        $podcast = Podcast::factory()->create(['is_visible' => true]);

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->podcastService->destroyPodcast($user, $podcast->id);
    }

    #[Test]
    public function itShouldSearchPodcastsByTitle()
    {
        $user = User::factory()->create();
        $fakeText = fake()->sentence(4);

        $podcast1 = Podcast::factory()->create([
            'title' => $fakeText,
            'is_visible' => true
        ]);
        $podcast2 = Podcast::factory()->create([
            'description' => $fakeText,
            'is_visible' => true
        ]);
        $user->podcasts()->attach([$podcast1, $podcast2]);

        $filters = [
            'query' => $fakeText,
            'filter_by' => 'title'
        ];

        $podcasts = $this->podcastService->search($user, $filters);

        $this->assertCount(1, $podcasts->items());
        $this->assertEquals($podcast1->id, $podcasts->items()[0]->id);
        $this->assertEquals($podcast1->title, $podcasts->items()[0]->title);
    }

    #[Test]
    public function itShouldSearchPodcastsByDescription()
    {
        $user = User::factory()->create();
        $fakeText = fake()->sentence(4);

        $podcast1 = Podcast::factory()->create([
            'description' => $fakeText,
            'is_visible' => true
        ]);
        $podcast2 = Podcast::factory()->create([
            'title' => $fakeText,
            'is_visible' => true
        ]);
        $user->podcasts()->attach([$podcast1, $podcast2]);

        $filters = [
            'query' => $fakeText,
            'filter_by' => 'description'
        ];

        $podcasts = $this->podcastService->search($user, $filters);

        $this->assertCount(1, $podcasts->items());
        $this->assertEquals($podcast1->id, $podcasts->items()[0]->id);
        $this->assertEquals($podcast1->title, $podcasts->items()[0]->title);
    }

    #[Test]
    public function itShouldSearchPodcastsByTitleAndDescription()
    {
        $user = User::factory()->create();
        $fakeText = fake()->sentence(4);

        $podcast1 = Podcast::factory()->create([
            'description' => $fakeText,
            'is_visible' => true
        ]);
        $podcast2 = Podcast::factory()->create([
            'title' => $fakeText,
            'is_visible' => true
        ]);
        $user->podcasts()->attach([$podcast1, $podcast2]);

        $filters = [
            'query' => $fakeText,
            'filter_by' => 'both'
        ];

        $podcasts = $this->podcastService->search($user, $filters);

        $this->assertCount(2, $podcasts->items());
        $this->assertEquals($podcast1->id, $podcasts->items()[0]->id);
        $this->assertEquals($podcast1->title, $podcasts->items()[0]->title);

        $this->assertEquals($podcast2->id, $podcasts->items()[1]->id);
        $this->assertEquals($podcast2->title, $podcasts->items()[1]->title);
    }
}
