<?php

namespace Database\Factories;

use App\Models\Podcast;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Podcast>
 */
class PodcastFactory extends Factory
{
    protected $model = Podcast::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'description' => fake()->text(200),
            'author' => fake()->name(),
            'link' => fake()->url(),
            'image_url' => fake()->imageUrl(600, 600, 'podcast'),
            'feed_url' => fake()->url(),
        ];
    }
}
