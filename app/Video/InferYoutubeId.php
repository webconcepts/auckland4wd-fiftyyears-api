<?php

namespace App\Video;

/**
 * Infer a youtube video's id from it's URL
 */
class InferYoutubeId
{
    /**
     * @return mixed string id or null on failure
     */
    public function __invoke($url)
    {
        preg_match(
            "/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/",
            $url,
            $matches
        );

        return !empty($matches[1]) ? $matches[1] : null;
    }
}
