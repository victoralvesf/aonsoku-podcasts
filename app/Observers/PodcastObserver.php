<?php

namespace App\Observers;

use App\Models\Podcast;

class PodcastObserver
{
    /**
     * Handle the Podcast "created" event.
     */
    public function created(Podcast $podcast): void
    {
        $podcast::clearGetCountCache();
    }

    /**
     * Handle the Podcast "updated" event.
     */
    public function updated(Podcast $podcast): void
    {
        //
    }

    /**
     * Handle the Podcast "deleted" event.
     */
    public function deleted(Podcast $podcast): void
    {
        $podcast::clearGetCountCache();
    }

    /**
     * Handle the Podcast "restored" event.
     */
    public function restored(Podcast $podcast): void
    {
        $podcast::clearGetCountCache();
    }

    /**
     * Handle the Podcast "force deleted" event.
     */
    public function forceDeleted(Podcast $podcast): void
    {
        $podcast::clearGetCountCache();
    }
}
