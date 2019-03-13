<?php

namespace App\Video;

use Exception;
use App\Exceptions\VideoInfoFailedException;
use App\Exceptions\InvalidVideoTypeException;

/**
 * Get info about a youtube or vimeo video using their OEmbed endpoints
 */
class FakeVideoInfo implements VideoInfo
{
    /**
     * @var string video url
     */
    protected $url;

    public function __construct($url)
    {
        $this->url = $url;

        $parsedUrl = parse_url($url);

        if (empty($parsedUrl['host'])) {
            throw new VideoInfoFailedException();
        }

        if (stripos($parsedUrl['host'], 'youtube.com') !== false
            || stripos($parsedUrl['host'], 'youtu.be') !== false
        ) {
            $this->type = VideoInfo::TYPE_YOUTUBE;
        } elseif (stripos($parsedUrl['host'], 'vimeo.com') !== false) {
            $this->type = VideoInfo::TYPE_VIMEO;
        } else {
            throw new InvalidVideoTypeException();
        }
    }

    /**
     * Get the type of the video
     *
     * @return string youtube or vimeo
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the id of the video
     *
     * @return string
     */
    public function getId()
    {
        switch ($this->type) {
            case VideoInfo::TYPE_YOUTUBE:
                return app(InferYoutubeId::class)($this->url);
            case VideoInfo::TYPE_VIMEO:
                return app(InferVimeoId::class)($this->url);
        }
        return null;
    }

    /**
     * Get the thumbnail image url for this video
     *
     * @return string
     */
    public function getImageUrl()
    {
        return 'https://fake.thumbnails.'.$this->type.'.com/thumbnail/'.$this->getId().'.jpg';
    }
}
