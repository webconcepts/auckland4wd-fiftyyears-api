<?php

namespace App\Http\Controllers;

use App\User;
use App\PhotoAlbum;
use App\IdObfuscator;
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

    /**
     * Add a new photo album
     */
    public function store(Request $request)
    {
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
            ->header('Location', route('photoalbums.show', [
                'obfuscatedId' => $album->obfuscatedId()
            ]));
    }

    /**
     * Update value(s) for an existing unpublished photo album
     */
    public function update($obfuscatedId, Request $request)
    {
        $validData = $this->validate($request, [
            'title' => 'nullable',
            'date' => 'nullable|date_format:"Y-m-d"',
            'location' => 'nullable',
            'photographer' => 'nullable',
            'description' => 'nullable',
        ]);

        if (empty($validData)) {
            abort(400, 'No valid data provided');
        }

        $album = PhotoAlbum::unpublished()
            ->findOrFail(PhotoAlbum::actualId($obfuscatedId));

        $album->update($validData);

        return response(['data' => $album]);
    }

    /**
     * Remove an unpublished photo album
     */
    public function destroy($obfuscatedId, Request $request)
    {
        $album = PhotoAlbum::unpublished()
            ->findOrFail(PhotoAlbum::actualId($obfuscatedId))
            ->remove();

        return response('', 200);
    }
}
