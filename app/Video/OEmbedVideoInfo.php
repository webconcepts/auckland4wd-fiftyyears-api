<?php

namespace App\Video;

use Exception;
use Essence\Essence;
use App\Exceptions\VideoInfoFailedException;
use App\Exceptions\InvalidVideoTypeException;

/**
 * Get info about a youtube or vimeo video using their OEmbed endpoints
 */
class OEmbedVideoInfo implements VideoInfo
{
    /**
     * @var Essence\Media $info
     */
    protected $info;

    /**
     * @var string type of video, see VideoInfo type constants
     */
    protected $type;

    public function __construct($url)
    {
        try {
            $this->info = (new Essence())->extract($url);
            if ($this->info == null) {
                throw new Exception();
            }
        } catch (Exception $e) {
            throw new VideoInfoFailedException();
        }

        // only allow youtube and vimeo videos
        $this->type = strtolower($this->info->provider_name);
        if (!in_array($this->type, [VideoInfo::TYPE_YOUTUBE, VideoInfo::TYPE_VIMEO])) {
            throw new InvalidVideoTypeException($this->type.' is not a supported video type');
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
                return app(InferYoutubeId::class)($this->info->url);
            case VideoInfo::TYPE_VIMEO:
                return $this->info->video_id;
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
        return $this->info->thumbnail_url ? $this->info->thumbnail_url : null;
    }
}
