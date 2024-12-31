<?php

namespace App\Console\Commands;

use App\Models\Podcast;
use Illuminate\Console\Command;

class RecalculateEpisodeCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recalculate-episode-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Recalculating episode counts for all podcasts...');

        Podcast::withCount('episodes')->get()->each(function ($podcast) {
            $this->info("Updating {$podcast->title} with {$podcast->episodes_count} episodes");

            $podcast->update(['episode_count' => $podcast->episodes_count]);
        });

        $this->info('Episode counts recalculated successfully!');
    }
}
