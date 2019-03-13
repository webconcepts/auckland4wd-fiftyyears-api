<?php

namespace App\Http\Controllers;

use App\Item;

class MilestoneController extends ItemController
{
    protected $type = Item::MILESTONE;
}
