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

        $feedMock = Mockery::mock();
        $feedMock->shouldReceive('get_items')->andReturn([
            Mockery::mock(SimplePieItem::class, [
                'get_title' => 'Episode 1',
                'get_permalink' => 'https://example.com/episode1.mp3',
                'get_description' => 'Description for Episode 1',
                'get_enclosure' => Mockery::mock([
                    'get_link' => 'https://example.com/episode1.mp3'
                ]),
                'get_item_tags' => [],
                'get_date' => fake()->dateTime()->format('Y-m-d H:i:s'),
            ]),
            Mockery::mock(SimplePieItem::class, [
                'get_title' => 'Episode 2',
                'get_permalink' => 'https://example.com/episode2.mp3',
                'get_description' => 'Description for Episode 2',
                'get_enclosure' => Mockery::mock([
                    'get_link' => 'https://example.com/episode2.mp3'
                ]),
                'get_item_tags' => [],
                'get_date' => fake()->dateTime()->format('Y-m-d H:i:s'),
            ]),
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

        $feedMock = Mockery::mock();
        $feedMock->shouldReceive('get_items')->andReturn([
            Mockery::mock(SimplePieItem::class, [
                'get_title' => 'Episode 1',
                'get_permalink' => 'https://example.com/episode1.mp3',
                'get_description' => 'Description for Episode 1',
                'get_enclosure' => Mockery::mock([
                    'get_link' => 'https://example.com/episode1.mp3'
                ]),
                'get_item_tags' => [],
                'get_date' => fake()->dateTime()->format('Y-m-d H:i:s'),
            ]),
            Mockery::mock(SimplePieItem::class, [
                'get_title' => 'Episode 2',
                'get_permalink' => 'https://example.com/episode2.mp3',
                'get_description' => 'Description for Episode 2',
                'get_enclosure' => Mockery::mock([
                    'get_link' => 'https://example.com/episode2.mp3'
                ]),
                'get_item_tags' => [],
                'get_date' => fake()->dateTime()->format('Y-m-d H:i:s'),
            ]),
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

        $item1 = Mockery::mock(SimplePieItem::class);
        $item1->shouldReceive('get_title')->andReturn('Episode 1');
        $item1->shouldReceive('get_permalink')->andReturn('https://example.com/episode1.mp3');
        $item1->shouldReceive('get_description')->andReturn('Description for Episode 1');
        $item1->shouldReceive('get_enclosure')->andReturn(Mockery::mock([
            'get_link' => 'https://example.com/episode1.mp3'
        ]));
        $item1->shouldReceive('get_item_tags')->andThrow(new \Exception('Simulated error'));
        $item1->shouldReceive('get_date')->andReturn(fake()->dateTime()->format('Y-m-d H:i:s'));

        $item2 = Mockery::mock(SimplePieItem::class);
        $item2->shouldReceive('get_title')->andReturn('Episode 2');
        $item2->shouldReceive('get_permalink')->andReturn('https://example.com/episode2.mp3');
        $item2->shouldReceive('get_description')->andReturn('Description for Episode 2');
        $item2->shouldReceive('get_enclosure')->andReturn(Mockery::mock([
            'get_link' => 'https://example.com/episode2.mp3'
        ]));
        $item2->shouldReceive('get_item_tags')->andThrow(new \Exception('Simulated error'));
        $item2->shouldReceive('get_date')->andReturn(fake()->dateTime()->format('Y-m-d H:i:s'));

        $feedMock = Mockery::mock();
        $feedMock->shouldReceive('get_items')->andReturn([$item1, $item2]);

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
