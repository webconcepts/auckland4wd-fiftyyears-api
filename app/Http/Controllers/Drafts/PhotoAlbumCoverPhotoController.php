<?php

namespace App\Http\Controllers\Drafts;

use App\Item;
use App\Photo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PhotoAlbumCoverPhotoController extends Controller
{
    /**
     * Save a photo from the album as the cover photo
     */
    public function store($obfuscatedAlbumId, Request $request)
    {
        $album = $this->getAlbum($obfuscatedAlbumId);

        $this->validate($request, [
            'id' => 'required|integer'
        ]);

        $photo = $album->photos()->uploaded()
            ->findOrFail(Photo::actualId($request->input('id')));

        $album->coverPhoto()->associate($photo)->save();

        return response(['data' => $photo], 201);
    }

    /**
     * Remove the cover photo from this album
     */
    public function destroy($obfuscatedAlbumId, Request $request)
    {
        $this->getAlbum($obfuscatedAlbumId)
            ->coverPhoto()
            ->dissociate()
            ->save();
    }

    /**
     * Get the album and check the user is authorized to edit it
     *
     * @param int $obfuscatedAlbumId
     * @return App\PhotoAlbum
     */
    protected function getAlbum($obfuscatedAlbumId)
    {
        $album = Item::photoAlbum()->draft()
            ->findOrFail(Item::actualId($obfuscatedAlbumId));

        $this->authorize('edit', $album);

        return $album;
    }
}
