<?php

namespace App\Helpers;

use SimplePie\Item as SimplePieItem;
use SimplePie\SimplePie;

class PodcastItemHelper
{
    public static function getItunesImage(SimplePieItem $item)
    {
        $images = $item->get_item_tags(SimplePie::NAMESPACE_ITUNES, 'image');

        if (!empty($images)) {
            return $images[0]['attribs']['']['href'];
        } else {
            return $item->get_feed()->get_image_url();
        }
    }

    public static function getItunesDuration(SimplePieItem $item)
    {
        $duration = $item->get_item_tags(\SimplePie\SimplePie::NAMESPACE_ITUNES, 'duration')[0];
        $durationValue = is_array($duration) ? $duration['data'] : $duration;

        return PodcastItemHelper::convertDurationToSeconds($durationValue);
    }

    public static function convertDurationToSeconds($duration)
    {
        // If it's already a number (seconds), return it
        if (is_numeric($duration)) {
            return $duration;
        }

        // If it's a time string (HH:MM:SS or MM:SS)
        if (strpos($duration, ':') !== false) {
            $parts = array_reverse(explode(':', $duration));
            $seconds = 0;

            // Seconds
            if (isset($parts[0])) {
                $seconds += (int) $parts[0];
            }
            // Minutes
            if (isset($parts[1])) {
                $seconds += (int) $parts[1] * 60;
            }
            // Hours
            if (isset($parts[2])) {
                $seconds += (int) $parts[2] * 3600;
            }

            return $seconds;
        }

        return 0; // Return 0 if format is unrecognized
    }
}
