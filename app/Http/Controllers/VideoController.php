<?php

namespace App\Http\Controllers;

use App\Item;

class VideoController extends ItemController
{
    protected $type = Item::VIDEO;
}
