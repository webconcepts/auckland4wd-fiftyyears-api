<?php

namespace App\Http\Controllers\Drafts;

use App\User;
use App\PhotoAlbum;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PhotoAlbumController extends Controller
{
    /**
     * Retrieve an individual draft photo album
     */
    public function show($obfuscatedId)
    {
        //
    }

    /**
     * Add a new draft photo album
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
            ->header('Location', route('drafts.photoalbums.show', [
                'obfuscatedId' => $album->obfuscatedId()
            ]));
    }

    /**
     * Update value(s) for an existing draft photo album
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

        $album = PhotoAlbum::draft()
            ->findOrFail(PhotoAlbum::actualId($obfuscatedId));

        $album->update($validData);

        return response(['data' => $album]);
    }

    /**
     * Remove a draft photo album
     */
    public function destroy($obfuscatedId, Request $request)
    {
        $album = PhotoAlbum::draft()
            ->findOrFail(PhotoAlbum::actualId($obfuscatedId))
            ->remove();

        return response('', 200);
    }
}