<?php

namespace App\Http\Controllers;

use App\Item;
use App\Photo;
use Illuminate\Http\Request;

class PhotoAlbumPhotoLikesController extends Controller
{
    /**
     * Get a photo record for this published photo album.
     */
    public function store(Request $request, $obfuscatedAlbumId, $obfuscatedId)
    {
        $this->validate($request, [
            'likes' => 'integer|min:1|required'
        ]);

        $photo = $this->getAlbum($obfuscatedAlbumId)
            ->photos()
            ->findOrFail(Photo::actualId($obfuscatedId));

        $photo->increment('likes', $request->input('likes'));

        return [
            'data' => ['likes' => $photo->likes]
        ];
    }

    /**
     * Get the published album
     *
     * @param int $obfuscatedAlbumId
     * @return App\PhotoAlbum
     */
    protected function getAlbum($obfuscatedAlbumId)
    {
        $album = Item::photoAlbum()->published()
            ->findOrFail(Item::actualId($obfuscatedAlbumId));

        return $album;
    }
}
