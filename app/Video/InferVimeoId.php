<?php

namespace App\Video;

/**
 * Infer a vimeo video's id from it's URL
 */
class InferVimeoId
{
    /**
     * @return mixed string id or null on failure
     */
    public function __invoke($url)
    {
        preg_match(
            '/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/',
            $url,
            $matches
        );

        return !empty($matches[5]) ? $matches[5] : null;
    }
}
