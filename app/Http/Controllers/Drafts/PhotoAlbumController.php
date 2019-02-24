<?php

namespace App\Http\Controllers\Drafts;

use App\User;
use App\PhotoAlbum;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PhotoAlbumController extends Controller
{
    /**
     * Retrieve a list of photo albums
     */
    public function index()
    {
        return ['data' => Auth::user()->draftPhotoAlbums()->get()];
    }

    /**
     * Retrieve an individual draft photo album
     */
    public function show($obfuscatedId)
    {
        return ['data' => $this->getAlbum($obfuscatedId)];
    }

    /**
     * Add a new draft photo album
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required'
        ]);

        $album = PhotoAlbum::create([
            'title' => $request->input('title'),
            'user_id' => Auth::user()->id
        ]);

        return response(['data' => $album], 201)
            ->header('Location', route('drafts.photoalbums.show', ['obfuscatedId' => $album->obfuscatedId()]));
    }

    /**
     * Update value(s) for an existing draft photo album
     */
    public function update($obfuscatedId, Request $request)
    {
        $album = $this->getAlbum($obfuscatedId);

        $album->update($this->validDataOrAbort($request, [
            'title' => 'nullable',
            'date' => 'nullable|date_format:"Y-m-d"',
            'location' => 'nullable',
            'photographer' => 'nullable',
            'description' => 'nullable',
        ]));

        return ['data' => $album];
    }

    /**
     * Remove a draft photo album
     */
    public function destroy($obfuscatedId, Request $request)
    {
        $this->getAlbum($obfuscatedId)
            ->remove();
    }

    /**
     * Get the album and check the user is authorized to edit it
     *
     * @param int $obfuscatedId
     * @return App\PhotoAlbum
     */
    protected function getAlbum($obfuscatedId)
    {
        $album = PhotoAlbum::draft()
            ->findOrFail(PhotoAlbum::actualId($obfuscatedId));

        $this->authorize('edit', $album);

        return $album;
    }
}
