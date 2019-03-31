<?php

namespace App\Http\Controllers;

use App\Item;
use App\Photo;

class PhotoAlbumPhotoController extends Controller
{
    /**
     * Get a list of photos for this published photo album that are uploaded and
     * not removed.
     */
    public function index($obfuscatedAlbumId)
    {
        return [
            'data' => $this->getAlbum($obfuscatedAlbumId)
                ->photos()
                ->uploaded()
                ->orderBy('number')
                ->get()
        ];
    }

    /**
     * Get a photo record for this published photo album.
     */
    public function show($obfuscatedAlbumId, $obfuscatedId)
    {
        $photo = $this->getAlbum($obfuscatedAlbumId)
            ->photos()
            ->findOrFail(Photo::actualId($obfuscatedId));

        $next = $photo->next();
        $previous = $photo->previous();

        return [
            'data' => $photo,
            'next' => $next ? $next->obfuscatedId() : null,
            'previous' => $previous ? $previous->obfuscatedId() : null,
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
