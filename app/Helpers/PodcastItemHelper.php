<?php

namespace App\Helpers;

use App\Models\Podcast;
use RuntimeException;
use SimplePie\Item as SimplePieItem;
use SimplePie\SimplePie;

class PodcastItemHelper
{
    public static function formatEpisode(SimplePieItem $item, Podcast $podcast): array
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

    public static function formatTitle(string $title): string
    {
        $formatted_title = html_entity_decode($title);
        $formatted_title = str_replace(["\n", "\r"], ' ', $formatted_title);
        $formatted_title = preg_replace('/\s+/', ' ', $formatted_title);

        return trim($formatted_title);
    }

    public static function getAudioUrl(SimplePieItem $item): string
    {
        $audio_url = null;

        foreach ($item->get_enclosures() as $enclosure) {
            if (str_starts_with($enclosure->get_type(), 'audio/')) {
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

    public static function getItunesDuration(SimplePieItem $item): int
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

    public static function convertDurationToSeconds(string $duration): int
    {
        // If it's already a number (seconds), return it
        if (is_numeric($duration)) {
            return (int) $duration;
        }

        // If it's a time string (HH:MM:SS or MM:SS)
        if (str_contains($duration, ':')) {
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

            return (int) $seconds;
        }

        return 0; // Return 0 if format is unrecognized
    }

    public static function formatPodcast(SimplePie $feed, string $feed_url): array
    {
        $title = self::formatTitle($feed->get_title());
        $description = self::formatTitle($feed->get_description() ?? '');
        $author = $feed->get_author()->name ?? '';
        $link = $feed->get_link() ?? '';
        $image_url = $feed->get_image_url();

        return [
            'title' => $title,
            'description' => $description,
            'author' => $author,
            'link' => $link,
            'image_url' => $image_url,
            'feed_url' => $feed_url,
        ];
    }
}
