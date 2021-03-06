<?php

namespace InstagramScraper;

class Media
{
    public $id;
    public $createdTime;
    public $type;
    public $link;
    public $imageLowResolutionUrl;
    public $imageThumbnailUrl;
    public $imageStandardResolutionUrl;
    public $imageHighResolutionUrl;
    public $caption;
    public $videoLowResolutionUrl;
    public $videoStandardResolutionUrl;
    public $videoLowBandwidthUrl;
    public $code;
    public $owner;

    function __construct()
    {
    }

    public static function fromApi($mediaArray)
    {
        $instance = new self();
        $instance->id = $mediaArray['id'];
        $instance->type = $mediaArray['type'];
        $instance->createdTime = $mediaArray['created_time'];
        $instance->code = $mediaArray['code'];
        $instance->link = $mediaArray['link'];
        $instance->imageLowResolutionUrl = self::getCleanImageUrl($mediaArray['images']['low_resolution']['url']);
        $instance->imageThumbnailUrl = self::getCleanImageUrl($mediaArray['images']['thumbnail']['url']);
        $instance->imageStandardResolutionUrl = self::getCleanImageUrl($mediaArray['images']['standard_resolution']['url']);
        $instance->imageHighResolutionUrl = str_replace('320x320', '1080x1080', $instance->imageLowResolutionUrl);
        if (isset($mediaArray['caption'])) {
            $instance->caption = $mediaArray['caption']['text'];
        }
        if ($instance->type === 'video') {
            $instance->videoLowResolutionUrl = $mediaArray['videos']['low_resolution']['url'];
            $instance->videoStandardResolutionUrl = $mediaArray['videos']['standard_resolution']['url'];
            $instance->videoLowBandwidthUrl = $mediaArray['videos']['low_bandwidth']['url'];
        }
        return $instance;
    }

    private static function getCleanImageUrl($imageUrl)
    {
        return strpos($imageUrl, '?ig_cache_key=') ? substr($imageUrl, 0, strpos($imageUrl, '?ig_cache_key=')) : $imageUrl;
    }

    public static function fromMediaPage($mediaArray)
    {
        $instance = new self();
        $instance->id = $mediaArray['id'];
        $instance->type = 'image';
        if ($mediaArray['is_video']) {
            $instance->type = 'video';
            $instance->videoStandardResolutionUrl = $mediaArray['video_url'];
        }
        $instance->createdTime = $mediaArray['date'];
        $instance->code = $mediaArray['code'];
        $instance->link = Instagram::INSTAGRAM_URL . 'p/' . $instance->code;
        $images = self::getImageUrls($mediaArray['display_src']);
        $instance->imageStandardResolutionUrl = $images['standard'];
        $instance->imageLowResolutionUrl = $images['low'];
        $instance->imageHighResolutionUrl = $images['high'];
        $instance->imageThumbnailUrl = $images['thumbnail'];
        if (isset($mediaArray['caption'])) {
            $instance->caption = $mediaArray['caption'];
        }
        $instance->owner = Account::fromMediaPage($mediaArray['owner']);
        return $instance;
    }

    private static function getImageUrls($imageUrl)
    {
        $imageUrl = self::getCleanImageUrl($imageUrl);
        $imageUrl = str_replace('s480x480/', "", $imageUrl);
        $parts = explode('/', parse_url($imageUrl)['path']);
        $standard = 'https://scontent.cdninstagram.com/' . $parts[1] . '/s640x640/' . $parts[2] . '/' . $parts[3];
        $urls = [
            'standard' => $standard,
            'low' => str_replace('640x640', '320x320', $standard),
            'high' => str_replace('640x640', '1080x1080', $standard),
            'thumbnail' => str_replace('640x640', '150x150', $standard)
        ];
        return $urls;
    }
}