<?php

namespace App\Helpers;

use App\Models\Podcast;
use RuntimeException;
use SimplePie\Item as SimplePieItem;
use SimplePie\SimplePie;

class PodcastItemHelper
{
    public static function formatEpisode(SimplePieItem $item, Podcast $podcast)
    {
        return [
            'podcast_id' => $podcast->id,
            'title' => self::formatTitle($item->get_title()),
            'description' => $item->get_content() ?? '',
            'audio_url' => self::getAudioUrl($item),
            'image_url' => self::getItunesImage($item, $podcast->image_url),
            'duration' => self::getItunesDuration($item),
            'published_at' => self::getPublishDate($item),
        ];
    }

    public static function formatTitle(string $title)
    {
        $formatted_title = html_entity_decode($title);
        $formatted_title = str_replace(["\n", "\r"], ' ', $formatted_title);
        $formatted_title = preg_replace('/\s+/', ' ', $formatted_title);
        $formatted_title = trim($formatted_title);

        return $formatted_title;
    }

    public static function getAudioUrl(SimplePieItem $item)
    {
        $audio_url = null;

        foreach ($item->get_enclosures() as $enclosure) {
            if (strpos($enclosure->get_type(), 'audio/') === 0) {
                $audio_url = $enclosure->get_link();
                break;
            }
        }

        if (!$audio_url) {
            throw new RuntimeException('No audio_url found for the episode.');
        }

        return $audio_url;
    }

    public static function getItunesImage(SimplePieItem $item, string $defaultImage)
    {
        $images = $item->get_item_tags(SimplePie::NAMESPACE_ITUNES, 'image');

        if (empty($images) || !isset($images[0]['attribs']['']['href'])) {
            return $defaultImage;
        }

        $url = $images[0]['attribs']['']['href'];

        return !empty($url) ? $url : $defaultImage;
    }

    public static function getItunesDuration(SimplePieItem $item)
    {
        $duration = $item->get_item_tags(SimplePie::NAMESPACE_ITUNES, 'duration');

        if (is_array($duration) && !empty($duration)) {
            $durationItem = $duration[0];
            $durationValue = is_array($durationItem) ? $durationItem['data'] : $durationItem;

            return self::convertDurationToSeconds($durationValue);
        }

        return 0;
    }

    public static function getPublishDate(SimplePieItem $item)
    {
        return $item->get_date('Y-m-d H:i:s');
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
