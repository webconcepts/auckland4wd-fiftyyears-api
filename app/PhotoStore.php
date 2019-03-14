<?php

namespace App;

use App\Photo;

interface PhotoStore
{
    public function getKey(Photo $photo);

    public function putFileFromURL(Photo $photo, $url);
}
