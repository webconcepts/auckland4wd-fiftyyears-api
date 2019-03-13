<?php

namespace App\Http\Controllers;

use App\Item;

class PhotoAlbumController extends ItemController
{
    protected $type = Item::PHOTO_ALBUM;
}
