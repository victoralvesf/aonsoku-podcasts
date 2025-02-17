<?php

namespace Tests\Unit;

use App\Jobs\TriggerPodcastsUpdate;
use App\Jobs\UpdatePodcastEpisodes;
use App\Models\Podcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TriggerPodcastsUpdateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function itShouldDispatchUpdatePodcastEpisodesJobForEachPodcast()
    {
        Bus::fake();

        Podcast::factory()->count(3)->create([
            'is_visible' => true
        ]);

        $job = new TriggerPodcastsUpdate();
        $job->handle();

        Bus::assertDispatchedTimes(UpdatePodcastEpisodes::class, 3);
    }
}
