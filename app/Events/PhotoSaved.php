<?php

namespace App\Events;

use App\Photo;

class PhotoSaved extends Event
{
    public $photo;

    public function __construct(Photo $photo)
    {
        $this->photo = $photo;
    }
}
