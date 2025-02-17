<?php

namespace Tests\Unit;

use App\Jobs\ProcessPodcastEpisodes;
use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use SimplePie\Item as SimplePieItem;
use SimplePie\Enclosure as SimplePieEnclosure;
use SimplePie\SimplePie;
use Tests\TestCase;
use willvincent\Feeds\Facades\FeedsFacade;

class ProcessPodcastEpisodesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function itShouldProcessPodcastEpisodesSuccessfully()
    {
        $podcast = Podcast::factory()->create([
            'feed_url' => 'http://fakefeed.com/rss'
        ]);

        $enclosure1 = Mockery::mock(SimplePieEnclosure::class);
        $enclosure1->shouldReceive('get_type')->andReturn('audio/mpeg');
        $enclosure1->shouldReceive('get_link')->andReturn('https://example.com/episode1.mp3');

        $enclosure2 = Mockery::mock(SimplePieEnclosure::class);
        $enclosure2->shouldReceive('get_type')->andReturn('audio/mpeg');
        $enclosure2->shouldReceive('get_link')->andReturn('https://example.com/episode2.mp3');

        $feedMock = Mockery::mock(SimplePie::class);
        $feedMock->shouldReceive('get_items')->andReturn([
            Mockery::mock(SimplePieItem::class, function ($mock) use ($enclosure1) {
                $mock->shouldReceive('get_title')->andReturn('Episode 1');
                $mock->shouldReceive('get_content')->andReturn('Description for Episode 1');
                $mock->shouldReceive('get_enclosures')->andReturn([$enclosure1]);
                $mock->shouldReceive('get_item_tags')->andReturn([]);
                $mock->shouldReceive('get_date')->andReturn(fake()->dateTime()->format('Y-m-d H:i:s'));
            }),
            Mockery::mock(SimplePieItem::class, function ($mock) use ($enclosure2) {
                $mock->shouldReceive('get_title')->andReturn('Episode 2');
                $mock->shouldReceive('get_content')->andReturn('Description for Episode 2');
                $mock->shouldReceive('get_enclosures')->andReturn([$enclosure2]);
                $mock->shouldReceive('get_item_tags')->andReturn([]);
                $mock->shouldReceive('get_date')->andReturn(fake()->dateTime()->format('Y-m-d H:i:s'));
            }),
        ]);

        FeedsFacade::shouldReceive('make')
            ->with($podcast->feed_url)
            ->andReturn($feedMock);

        $job = new ProcessPodcastEpisodes($podcast);
        $job->handle();

        $this->assertDatabaseCount(Episode::class, 2);
        $this->assertTrue($podcast->fresh()->is_visible);
    }

    #[Test]
    public function itShouldNotProcessPodcastEpisodesIfAlreadyExists()
    {
        $podcast = Podcast::factory()->create([
            'feed_url' => 'http://fakefeed.com/rss'
        ]);
        Episode::factory()->create([
            'podcast_id' => $podcast->id,
            'audio_url' => 'https://example.com/episode1.mp3'
        ]);

        $enclosure1 = Mockery::mock(SimplePieEnclosure::class);
        $enclosure1->shouldReceive('get_type')->andReturn('audio/mpeg');
        $enclosure1->shouldReceive('get_link')->andReturn('https://example.com/episode1.mp3');

        $enclosure2 = Mockery::mock(SimplePieEnclosure::class);
        $enclosure2->shouldReceive('get_type')->andReturn('audio/mpeg');
        $enclosure2->shouldReceive('get_link')->andReturn('https://example.com/episode2.mp3');

        $feedMock = Mockery::mock(SimplePie::class);
        $feedMock->shouldReceive('get_items')->andReturn([
            Mockery::mock(SimplePieItem::class, function ($mock) use ($enclosure1) {
                $mock->shouldReceive('get_title')->andReturn('Episode 1');
                $mock->shouldReceive('get_content')->andReturn('Description for Episode 1');
                $mock->shouldReceive('get_enclosures')->andReturn([$enclosure1]);
                $mock->shouldReceive('get_item_tags')->andReturn([]);
                $mock->shouldReceive('get_date')->andReturn(fake()->dateTime()->format('Y-m-d H:i:s'));
            }),
            Mockery::mock(SimplePieItem::class, function ($mock) use ($enclosure2) {
                $mock->shouldReceive('get_title')->andReturn('Episode 2');
                $mock->shouldReceive('get_content')->andReturn('Description for Episode 2');
                $mock->shouldReceive('get_enclosures')->andReturn([$enclosure2]);
                $mock->shouldReceive('get_item_tags')->andReturn([]);
                $mock->shouldReceive('get_date')->andReturn(fake()->dateTime()->format('Y-m-d H:i:s'));
            }),
        ]);

        FeedsFacade::shouldReceive('make')
            ->with($podcast->feed_url)
            ->andReturn($feedMock);

        $job = new ProcessPodcastEpisodes($podcast);
        $job->handle();

        $this->assertDatabaseCount(Episode::class, 2);
        $this->assertDatabaseMissing(Episode::class, [
            'title' => 'Episode 1',
            'podcast_id' => $podcast->id,
            'audio_url' => 'https://example.com/episode1.mp3'
        ]);
        $this->assertTrue($podcast->fresh()->is_visible);
    }

    #[Test]
    public function itShouldNotProcessPodcastEpisodesIfTheyDoNotHaveAudioUrl()
    {
        $podcast = Podcast::factory()->create([
            'feed_url' => 'http://fakefeed.com/rss'
        ]);

        $enclosure1 = Mockery::mock(SimplePieEnclosure::class);
        $enclosure1->shouldReceive('get_type')->andReturn('image/png');
        $enclosure1->shouldReceive('get_link')->andReturn('https://example.com/cover1.png');

        $enclosure2 = Mockery::mock(SimplePieEnclosure::class);
        $enclosure2->shouldReceive('get_type')->andReturn('image/png');
        $enclosure2->shouldReceive('get_link')->andReturn('https://example.com/cover2.png');

        $feedMock = Mockery::mock(SimplePie::class);
        $feedMock->shouldReceive('get_items')->andReturn([
            Mockery::mock(SimplePieItem::class, function ($mock) use ($enclosure1) {
                $mock->shouldReceive('get_title')->andReturn('Episode 1');
                $mock->shouldReceive('get_content')->andReturn('Description for Episode 1');
                $mock->shouldReceive('get_enclosures')->andReturn([$enclosure1]);
                $mock->shouldReceive('get_item_tags')->andReturn([]);
                $mock->shouldReceive('get_date')->andReturn(fake()->dateTime()->format('Y-m-d H:i:s'));
            }),
            Mockery::mock(SimplePieItem::class, function ($mock) use ($enclosure2) {
                $mock->shouldReceive('get_title')->andReturn('Episode 2');
                $mock->shouldReceive('get_content')->andReturn('Description for Episode 2');
                $mock->shouldReceive('get_enclosures')->andReturn([$enclosure2]);
                $mock->shouldReceive('get_item_tags')->andReturn([]);
                $mock->shouldReceive('get_date')->andReturn(fake()->dateTime()->format('Y-m-d H:i:s'));
            }),
        ]);

        FeedsFacade::shouldReceive('make')
            ->with($podcast->feed_url)
            ->andReturn($feedMock);

        $job = new ProcessPodcastEpisodes($podcast);
        $job->handle();

        $this->assertDatabaseCount(Episode::class, 0);
        $this->assertTrue($podcast->fresh()->is_visible);
    }

    #[Test]
    public function itShouldLogErrorWhenFeedFails()
    {
        $podcast = Podcast::factory()->create([
            'feed_url' => 'http://invalid-feed.com/rss'
        ]);

        FeedsFacade::shouldReceive('make')
            ->once()
            ->with($podcast->feed_url)
            ->andThrow(new \Exception('Feed not reachable'));

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) use ($podcast) {
                return $context['id'] === $podcast->id && $context['error'] === 'Feed not reachable';
            });

        $job = new ProcessPodcastEpisodes($podcast);
        $job->handle();

        $this->assertDatabaseCount(Episode::class, 0);
        $this->assertFalse($podcast->fresh()->is_visible);
    }

    #[Test]
    public function itShouldLogErrorWhenEpisodeFails()
    {
        $podcast = Podcast::factory()->create([
            'feed_url' => 'http://fakefeed.com/rss'
        ]);

        $enclosure1 = Mockery::mock(SimplePieEnclosure::class);
        $enclosure1->shouldReceive('get_type')->andReturn('audio/mpeg');
        $enclosure1->shouldReceive('get_link')->andReturn('https://example.com/episode1.mp3');

        $enclosure2 = Mockery::mock(SimplePieEnclosure::class);
        $enclosure2->shouldReceive('get_type')->andReturn('audio/mpeg');
        $enclosure2->shouldReceive('get_link')->andReturn('https://example.com/episode2.mp3');

        $feedMock = Mockery::mock(SimplePie::class);
        $feedMock->shouldReceive('get_items')->andReturn([
            Mockery::mock(SimplePieItem::class, function ($mock) use ($enclosure1) {
                $mock->shouldReceive('get_title')->andReturn('Episode 1');
                $mock->shouldReceive('get_content')->andReturn('Description for Episode 1');
                $mock->shouldReceive('get_enclosures')->andReturn([$enclosure1]);
                $mock->shouldReceive('get_item_tags')->andThrow(new \Exception('Simulated error'));
                $mock->shouldReceive('get_date')->andReturn(fake()->dateTime()->format('Y-m-d H:i:s'));
            }),
            Mockery::mock(SimplePieItem::class, function ($mock) use ($enclosure2) {
                $mock->shouldReceive('get_title')->andReturn('Episode 2');
                $mock->shouldReceive('get_content')->andReturn('Description for Episode 2');
                $mock->shouldReceive('get_enclosures')->andReturn([$enclosure2]);
                $mock->shouldReceive('get_item_tags')->andThrow(new \Exception('Simulated error'));
                $mock->shouldReceive('get_date')->andReturn(fake()->dateTime()->format('Y-m-d H:i:s'));
            }),
        ]);

        FeedsFacade::shouldReceive('make')
            ->with($podcast->feed_url)
            ->andReturn($feedMock);

        Log::shouldReceive('error')
            ->twice()
            ->withArgs(function ($message, $context) use ($podcast) {
                return $context['podcast_id'] === $podcast->id && $context['error'] === 'Simulated error';
            });

        $job = new ProcessPodcastEpisodes($podcast);
        $job->handle();

        $this->assertDatabaseCount(Episode::class, 0);
        $this->assertTrue($podcast->fresh()->is_visible);
    }
}
