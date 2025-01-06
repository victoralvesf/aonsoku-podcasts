<?php

namespace Tests\Feature;

use App\Models\Podcast;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

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
}
