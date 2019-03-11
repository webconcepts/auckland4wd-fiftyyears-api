<?php

namespace App\Http\Controllers;

use App\Item;
use Illuminate\Http\Request;

class PhotoAlbumController extends Controller
{
    /**
     * Retrieve a list of photo albums
     */
    public function index()
    {
        return ['data' => Item::published()->get()];
    }

    /**
     * Retrieve an individual photo album
     */
    public function show($obfuscatedId)
    {
        return [
            'data' => Item::photoAlbum()->published()
                ->findOrFail(Item::actualId($obfuscatedId))
        ];
    }
}
