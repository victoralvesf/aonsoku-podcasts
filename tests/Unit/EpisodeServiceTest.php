<?php

namespace Tests\Unit;

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\User;
use App\Services\EpisodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class EpisodeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EpisodeService $episodeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->episodeService = app(EpisodeService::class);
    }

    #[Test]
    public function itCanSearchEpisodesFromAPodcastByTitleAndDescription()
    {
        $user = User::factory()->create();
        $podcast = Podcast::factory()->create(['is_visible' => true]);
        $user->podcasts()->attach($podcast);

        $episode1 = Episode::factory()->create([
            'podcast_id' => $podcast->id,
            'title' => 'My Fake Podcast',
            'published_at' => now()->subDays(1),
        ]);
        $episode2 = Episode::factory()->create([
            'podcast_id' => $podcast->id,
            'description' => 'This is a Fake Podcast description',
            'published_at' => now()->subDays(2),
        ]);

        $filters = ['query' => 'Fake Podcast'];

        $episodes = $this->episodeService->searchPodcastEpisodes($user, $podcast->id, $filters);

        $this->assertCount(2, $episodes->items());
        $this->assertEquals(
            $episode1->title,
            $episodes->items()[0]->title,
            'The episode search brought unwanted results.'
        );
        $this->assertEquals(
            $episode2->description,
            $episodes->items()[1]->description,
            'The episode search brought unwanted results.'
        );
    }

    #[Test]
    public function itCanSearchEpisodesFromAPodcastByTitle()
    {
        $user = User::factory()->create();
        $podcast = Podcast::factory()->create(['is_visible' => true]);
        $user->podcasts()->attach($podcast);

        $episodeTitle = 'FakeCast 98 - My fake episode';
        $episode = Episode::factory()->create([
            'podcast_id' => $podcast->id,
            'title' => $episodeTitle,
        ]);
        Episode::factory()->create([
            'podcast_id' => $podcast->id
        ]);

        $filters = [
            'query' => 'My fake episode',
            'filter_by' => 'title'
        ];

        $episodes = $this->episodeService->searchPodcastEpisodes($user, $podcast->id, $filters);

        $this->assertCount(1, $episodes->items());
        $this->assertEquals(
            $episode->title,
            $episodes->items()[0]->title,
            'The episode search brought unwanted results.'
        );
    }

    #[Test]
    public function itCanSearchEpisodesFromAPodcastByDescription()
    {
        $user = User::factory()->create();
        $podcast = Podcast::factory()->create(['is_visible' => true]);
        $user->podcasts()->attach($podcast);

        $episode = Episode::factory()->create([
            'podcast_id' => $podcast->id,
            'description' => 'This is a fake description',
        ]);
        Episode::factory()->create([
            'podcast_id' => $podcast->id,
            'description' => 'This is a valid description',
        ]);

        $filters = [
            'query' => 'fake description',
            'filter_by' => 'description'
        ];

        $episodes = $this->episodeService->searchPodcastEpisodes($user, $podcast->id, $filters);

        $this->assertCount(1, $episodes->items());
        $this->assertEquals(
            $episode->description,
            $episodes->items()[0]->description,
            'The episode search brought unwanted results.'
        );
    }

    #[Test]
    public function itFailsToSearchIfPodcastIsNotFollowed()
    {
        $user = User::factory()->create();
        $podcast = Podcast::factory()->create();

        $filters = ['query' => 'fake description'];

        $this->expectException(NotFoundHttpException::class);
        $episodes = $this->episodeService->searchPodcastEpisodes($user, $podcast->id, $filters);
    }

    #[Test]
    public function itFailsToSearchIfPodcastDoesNotExist()
    {
        $user = User::factory()->create();

        $filters = ['query' => 'fake description'];

        $this->expectException(NotFoundHttpException::class);
        $episodes = $this->episodeService->searchPodcastEpisodes($user, '9', $filters);
    }

    #[Test]
    public function itCanGetLatestEpisodesFromFollowedPodcasts()
    {
        $user = User::factory()->create();

        $podcast1 = Podcast::factory()->create(['is_visible' => true]);
        $podcast2 = Podcast::factory()->create(['is_visible' => true]);

        $user->podcasts()->attach($podcast1);
        $user->podcasts()->attach($podcast2);

        $episode1 = Episode::factory()->create([
            'podcast_id' => $podcast1->id,
            'published_at' => now()->subDays(1),
        ]);
        Episode::factory()->create([
            'podcast_id' => $podcast1->id,
            'published_at' => now()->subDays(2),
        ]);
        $episode3 = Episode::factory()->create([
            'podcast_id' => $podcast2->id,
            'published_at' => now()->subDays(3),
        ]);

        $latestEpisodes = $this->episodeService->getLatestEpisodes($user);

        $this->assertCount(3, $latestEpisodes);
        $this->assertEquals(
            $episode1->id,
            $latestEpisodes->first()->id,
            'The first episode was not fetched correctly.'
        );
        $this->assertEquals(
            $episode3->id,
            $latestEpisodes->last()->id,
            'The last episode was not fetched correctly.'
        );
    }

    #[Test]
    public function itCanUpdateEpisodeProgressForAValidEpisode()
    {
        $user = User::factory()->create();
        $podcast = Podcast::factory()->create(['is_visible' => true]);

        $user->podcasts()->attach($podcast);

        $episode = Episode::factory()->create(['podcast_id' => $podcast->id]);

        $progress = 500;
        $playback = $this->episodeService->updateProgress($user, $episode->id, $progress);

        $this->assertEquals($progress, $playback->progress, 'The progress was not correctly updated.');
        $this->assertFalse($playback->completed, 'The episode should not be marked as completed.');
    }

    #[Test]
    public function itShouldFailWhenTryingToUpdateProgressForANonExistentEpisode()
    {
        $user = User::factory()->create();

        $this->expectException(NotFoundHttpException::class);
        $this->episodeService->updateProgress($user, '23', 100);
    }

    #[Test]
    public function itShouldGetSingleEpisodeForFollowedPodcast()
    {
        $user = User::factory()->create();
        $podcast = Podcast::factory()->create(['is_visible' => true]);
        $user->podcasts()->attach($podcast);

        $episode = Episode::factory()->create([
            'podcast_id' => $podcast->id,
            'title' => '#35 - Test Episode',
            'description' => 'Test Description',
            'audio_url' => 'https://fakecast.com/ep35.mp3',
            'image_url' => 'https://fakecast.com/ep35-thumbnail.jpg',
        ]);

        $result = $this->episodeService->getEpisode($user, $episode->id);

        $this->assertInstanceOf(Episode::class, $result);
        $this->assertEquals($episode->id, $result->id);
        $this->assertEquals($episode->title, $result->title);
        $this->assertEquals($episode->description, $result->description);
        $this->assertEquals($episode->audio_url, $result->audio_url);
        $this->assertEquals($episode->image_url, $result->image_url);
        $this->assertEquals($episode->podcast_id, $result->podcast_id);

        $this->assertTrue($result->podcast->is($podcast));
    }

    #[Test]
    public function itShouldThrowErrorGettingUnfollowedPodcastEpisode()
    {
        $user = User::factory()->create();
        $podcast = Podcast::factory()->create(['is_visible' => true]);

        $episode = Episode::factory()->create([
            'podcast_id' => $podcast->id,
            'title' => '#35 - Test Episode',
            'description' => 'Test Description',
            'audio_url' => 'https://fakecast.com/ep35.mp3',
            'image_url' => 'https://fakecast.com/ep35-thumbnail.jpg',
        ]);

        $this->expectException(NotFoundHttpException::class);

        $result = $this->episodeService->getEpisode($user, $episode->id);
    }

    #[Test]
    public function itShouldThrowErrorGettingANonExistentEpisode()
    {
        $user = User::factory()->create();

        $this->expectException(NotFoundHttpException::class);

        $result = $this->episodeService->getEpisode($user, '0194aa11-b51c-79ea-8826-b2278d0c67cc');
    }
}
