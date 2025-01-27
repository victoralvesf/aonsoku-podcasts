<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EpisodeTest extends TestCase
{
    use RefreshDatabase;

    protected array $headers;
    protected User $user;
    protected String $randomUuid;

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

        $this->randomUuid = (string) Str::uuid();
    }

    #[Test]
    public function itShouldSearchEpisodesFromAPodcast()
    {
        $podcast = Podcast::factory()->create(['is_visible' => true]);
        $this->user->podcasts()->attach($podcast);

        $episodeTitle = fake()->sentence(4);
        $episode = Episode::factory()->create([
            'title' => $episodeTitle,
            'podcast_id' => $podcast->id,
        ]);
        Episode::factory()->count(3)->create([
            'podcast_id' => $podcast->id,
        ]);

        $url = route('episodes.search', [
            'id' => $podcast->id,
            'query' => $episodeTitle,
        ]);
        $response = $this->getJson($url, $this->headers);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'id' => $episode->id,
                'title' => $episodeTitle,
            ]);
    }

    #[Test]
    public function itShouldSearchEpisodesFromAPodcastByTitle()
    {
        $podcast = Podcast::factory()->create(['is_visible' => true]);
        $this->user->podcasts()->attach($podcast);

        $episodeTitle = fake()->sentence(4);
        $episode = Episode::factory()->create([
            'title' => $episodeTitle,
            'podcast_id' => $podcast->id,
        ]);
        Episode::factory()->count(3)->create([
            'description' => $episodeTitle,
            'podcast_id' => $podcast->id,
        ]);

        $url = route('episodes.search', [
            'id' => $podcast->id,
            'query' => $episodeTitle,
            'filter_by' => 'title',
        ]);
        $response = $this->getJson($url, $this->headers);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'id' => $episode->id,
                'title' => $episodeTitle,
            ]);
    }

    #[Test]
    public function itShouldSearchEpisodesFromAPodcastByDescription()
    {
        $podcast = Podcast::factory()->create(['is_visible' => true]);
        $this->user->podcasts()->attach($podcast);

        $episodeDescription = fake()->sentence(4);
        $episode = Episode::factory()->create([
            'description' => $episodeDescription,
            'podcast_id' => $podcast->id,
        ]);
        $fakeSentence = fake()->sentence(3);
        Episode::factory()->count(3)->create([
            'title' => "{$fakeSentence} - {$episodeDescription}",
            'podcast_id' => $podcast->id,
        ]);

        $url = route('episodes.search', [
            'id' => $podcast->id,
            'query' => $episodeDescription,
            'filter_by' => 'description',
        ]);
        $response = $this->getJson($url, $this->headers);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'id' => $episode->id,
                'description' => $episodeDescription,
            ]);
    }

    #[Test]
    public function itShouldSortSearchedEpisodesFromAPodcastByAsc()
    {
        $podcast = Podcast::factory()->create(['is_visible' => true]);
        $this->user->podcasts()->attach($podcast);

        for ($i = 1; $i <= 3; $i++) {
            Episode::factory()->create([
                'title' => "Fake {$i}",
                'podcast_id' => $podcast->id,
                'published_at' => now()->subDays(4 - $i),
            ]);
        }

        $url = route('episodes.search', [
            'id' => $podcast->id,
            'query' => 'Fake',
            'sort' => 'asc',
        ]);
        $response = $this->getJson($url, $this->headers);

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.title', 'Fake 1')
            ->assertJsonPath('data.1.title', 'Fake 2')
            ->assertJsonPath('data.2.title', 'Fake 3');
    }

    #[Test]
    public function itShouldSortSearchedEpisodesFromAPodcastByDesc()
    {
        $podcast = Podcast::factory()->create(['is_visible' => true]);
        $this->user->podcasts()->attach($podcast);

        for ($i = 1; $i <= 3; $i++) {
            Episode::factory()->create([
                'title' => "Fake {$i}",
                'podcast_id' => $podcast->id,
                'published_at' => now()->subDays(4 - $i),
            ]);
        }

        $url = route('episodes.search', [
            'id' => $podcast->id,
            'query' => 'Fake',
            'sort' => 'desc',
        ]);
        $response = $this->getJson($url, $this->headers);

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.title', 'Fake 3')
            ->assertJsonPath('data.1.title', 'Fake 2')
            ->assertJsonPath('data.2.title', 'Fake 1');
    }

    #[Test]
    public function itShouldNotSearchEpisodesIfQueryIsLessThanThreeChars()
    {
        $url = route('episodes.search', [
            'id' => 1,
            'query' => 'Fa',
        ]);
        $response = $this->getJson($url, $this->headers);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('query');
    }

    #[Test]
    public function itShouldGetUserFollowedLatestEpisodes()
    {
        $podcast1 = Podcast::factory()->create(['is_visible' => true]);
        $podcast2 = Podcast::factory()->create(['is_visible' => true]);

        $this->user->podcasts()->attach([$podcast1, $podcast2]);

        Episode::factory()->create([
            'title' => 'First EP',
            'podcast_id' => $podcast1->id,
            'published_at' => now()->subDays(1),
        ]);
        Episode::factory()->create([
            'title' => 'Second EP',
            'podcast_id' => $podcast2->id,
            'published_at' => now()->subDays(2),
        ]);
        for ($i = 1; $i <= 50; $i++) {
            Episode::factory()->create([
                'title' => "Fake {$i}",
                'podcast_id' => $podcast1->id,
                'published_at' => now()->subDays(2 + $i),
            ]);
        }

        $url = route('episodes.latest');
        $response = $this->getJson($url, $this->headers);

        $response->assertOk()
            ->assertJsonCount(50)
            ->assertJsonPath('0.title', 'First EP')
            ->assertJsonPath('1.title', 'Second EP')
            ->assertJsonPath('49.title', 'Fake 48');
    }

    #[Test]
    public function itShouldSaveProgressToEpisode()
    {
        $podcast = Podcast::factory()->create([
            'is_visible' => true
        ]);
        $this->user->podcasts()->attach($podcast);
        $episode = Episode::factory()->create([
            'podcast_id' => $podcast->id,
            'duration' => 1200,
        ]);

        $url = route('episodes.progress', ['id' => $episode->id]);
        $body = ['progress' => 950];
        $response = $this->patchJson($url, $body, $this->headers);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'progress' => 950,
                'completed' => false,
            ]);
    }

    #[Test]
    public function itShouldSaveProgressToEpisodeWithCompleteDuration()
    {
        $podcast = Podcast::factory()->create([
            'is_visible' => true
        ]);
        $this->user->podcasts()->attach($podcast);
        $episode = Episode::factory()->create([
            'podcast_id' => $podcast->id,
            'duration' => 1200,
        ]);

        $url = route('episodes.progress', ['id' => $episode->id]);
        $body = ['progress' => 1200];
        $response = $this->patchJson($url, $body, $this->headers);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'progress' => 1200,
                'completed' => true,
            ]);
    }

    #[Test]
    public function itShouldNotSaveEpisodeProgressIfBodyIsString()
    {
        $url = route('episodes.progress', ['id' => $this->randomUuid]);
        $body = ['progress' => 'string'];
        $response = $this->patchJson($url, $body, $this->headers);

        $response->assertStatus(422)
            ->assertJsonValidationErrorFor('progress');
    }

    #[Test]
    public function itShouldReturnErrorIfEpisodeDoesNotExist()
    {
        $url = route('episodes.progress', ['id' => $this->randomUuid]);
        $body = ['progress' => 1200];
        $response = $this->patchJson($url, $body, $this->headers);

        $response->assertStatus(404);
    }
}
