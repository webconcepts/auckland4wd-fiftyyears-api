<?php

namespace App\Http\Controllers;

use App\User;
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
    public function show($id)
    {
        return ['data' => PhotoAlbum::published()->findOrFail($id)];
    }

    /**
     * Add a new photo album
     */
    public function store(Request $request)
    {
        // validation
        $this->validate($request, [
            'title' => 'required',
            'user.email' => 'required|email'
        ]);

        $user = User::firstOrCreateByEmail(
            $request->input('user.email'),
            $request->input('user.name')
        );

        $album = PhotoAlbum::create([
            'title' => $request->input('title'),
            'user_id' => $user->id
        ]);

        return response(['data' => $album], 201)
            ->header('Location', route('photoalbums.show', ['id' => $album->id]));
    }
}
