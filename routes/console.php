<?php

use App\Jobs\TriggerPodcastsUpdate;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new TriggerPodcastsUpdate)->everyTwoHours();
