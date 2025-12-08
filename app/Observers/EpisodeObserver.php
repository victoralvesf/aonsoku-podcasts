<?php

namespace App\Observers;

use App\Models\Episode;

class EpisodeObserver
{
    /**
     * Handle the Episode "created" event.
     */
    public function created(Episode $episode): void
    {
        $episode::clearGetCountCache();
    }

    /**
     * Handle the Episode "updated" event.
     */
    public function updated(Episode $episode): void
    {
        //
    }

    /**
     * Handle the Episode "deleted" event.
     */
    public function deleted(Episode $episode): void
    {
        $episode::clearGetCountCache();
    }

    /**
     * Handle the Episode "restored" event.
     */
    public function restored(Episode $episode): void
    {
        $episode::clearGetCountCache();
    }

    /**
     * Handle the Episode "force deleted" event.
     */
    public function forceDeleted(Episode $episode): void
    {
        $episode::clearGetCountCache();
    }
}
