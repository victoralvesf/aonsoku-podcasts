<?php

namespace Database\Factories;

use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Episode>
 */
class EpisodeFactory extends Factory
{
    protected $model = Episode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'podcast_id' => Podcast::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->text(200),
            'audio_url' => fake()->url(),
            'image_url' => fake()->imageUrl(600, 600, 'podcast'),
            'duration' => fake()->numberBetween(1000, 13600),
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
