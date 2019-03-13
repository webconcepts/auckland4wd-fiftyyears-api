<?php

namespace App\Http\Controllers;

use App\Item;
use Illuminate\Http\Request;

abstract class ItemController extends Controller
{
    /**
     * @var int $type set a type for each child controller
     */
    //protected $type;

    /**
     * Retrieve a list of photo albums
     */
    public function index()
    {
        return ['data' => Item::published()->where('type', $this->type)->get()];
    }

    /**
     * Retrieve an individual photo album
     */
    public function show($obfuscatedId)
    {
        return [
            'data' => Item::published()
                ->where('type', $this->type)
                ->findOrFail(Item::actualId($obfuscatedId))
        ];
    }
}
