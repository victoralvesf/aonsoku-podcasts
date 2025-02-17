<?php

namespace App\Console\Commands;

use App\Jobs\TriggerPodcastsUpdate;
use Illuminate\Console\Command;

class UpdatePodcasts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'podcast:update-episodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update episodes for all saved podcasts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        TriggerPodcastsUpdate::dispatch();
        $this->info('Trigger to Update Episodes dispatched');
    }
}
