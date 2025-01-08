<?php

namespace Tests\Unit;

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EpisodeModel extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function itShouldIncrementEpisodeCount()
    {
        $user = User::factory()->create();
        $podcast = Podcast::factory()->create([
            'is_visible' => true
        ]);

        $this->assertEquals(0, $podcast->episode_count);

        $user->podcasts()->attach($podcast);

        Episode::factory()->count(34)->create([
            'podcast_id' => $podcast->id
        ]);

        $this->assertEquals(34, $podcast->fresh()->episode_count);
    }

    #[Test]
    public function itShouldDecrementEpisodeCount()
    {
        $user = User::factory()->create();
        $podcast = Podcast::factory()->create([
            'is_visible' => true
        ]);
        $user->podcasts()->attach($podcast);

        $this->assertEquals(0, $podcast->episode_count);

        $episodes = Episode::factory()->count(34)->create([
            'podcast_id' => $podcast->id
        ]);

        $this->assertEquals(34, $podcast->fresh()->episode_count);

        $episodes->first()->delete();

        $this->assertEquals(33, $podcast->fresh()->episode_count);
    }
}
