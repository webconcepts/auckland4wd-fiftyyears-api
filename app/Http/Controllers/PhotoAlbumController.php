<?php

namespace App\Http\Controllers;

use App\PhotoAlbum;
use Illuminate\Http\Request;

class PhotoAlbumController extends Controller
{
    /**
     * Retrieve a list of photo albums
     */
    public function index()
    {
        return ['data' => PhotoAlbum::published()->get()];
    }

    /**
     * Retrieve an individual photo album
     */
    public function show($obfuscatedId)
    {
        return [
            'data' => PhotoAlbum::published()
                ->findOrFail(PhotoAlbum::actualId($obfuscatedId))
        ];
    }
}
