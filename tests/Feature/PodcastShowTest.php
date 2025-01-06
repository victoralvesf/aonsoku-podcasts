<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PodcastShowTest extends TestCase
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
}
