<?php

namespace App\Video;

interface VideoInfo
{
    const TYPE_YOUTUBE = 'youtube';

    const TYPE_VIMEO = 'vimeo';

    public function getType();

    public function getId();

    public function getImageUrl();
}
