<?php

namespace App\Http\Controllers\Drafts;

use App\Item;

class MilestoneController extends ItemController
{
    protected $type = Item::MILESTONE;
}
