<?php

use App\Jobs\UpdatePodcastEpisodes;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new UpdatePodcastEpisodes)->everyTwoHours();
