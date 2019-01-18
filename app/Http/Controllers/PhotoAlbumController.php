<?php

namespace App\Http\Controllers;

use App\PhotoAlbum;

class PhotoAlbumController extends Controller
{
    /**
     * Retrieve an individual photo album
     */
    public function show($id)
    {
        return ['data' => PhotoAlbum::published()->findOrFail($id)];
    }
}
