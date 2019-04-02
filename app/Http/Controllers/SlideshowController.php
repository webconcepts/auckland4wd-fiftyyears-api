<?php

namespace App\Http\Controllers;

use App\Item;
use Illuminate\Http\Request;

class SlideshowController extends Controller
{
    /**
     * Get random images in an album for displaying in a slideshow
     *
     * @param integer offset
     * @param integer number
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'offset' => 'integer|min:1|nullable',
            'number' => 'integer|min:1|nullable',
        ]);

        // get random album, or next album by offset
        $offset = $request->input('offset', null);
        $album = $this->getAlbum($offset);
        $photos = $this->getPhotos($album, $request->input('number', 7));

        return [
            'data' => $album,
            'photos' => $photos,
            'offset' => $offset,
        ];
    }

    /**
     * Get an album, either randomly, or at a given offset
     *
     * @return Item
     */
    protected function getAlbum($offset = null)
    {
        $albums = Item::published()->where('type', Item::PHOTO_ALBUM);

        if ($offset) {
            return $albums->orderBy('date')->offset($offset - 1)->first();
        } else {
            return $albums->inRandomOrder()->first();
        }
    }

    /**
     * Get random photos in an album, giving preference to those with the
     * highest number of likes
     *
     * @param Item $album
     * @param int $number number of photos to get
     * @return Collection photos sorted by number
     */
    protected function getPhotos(Item $album, $number)
    {
        return $album->photos()
            ->orderBy('likes', 'desc')
            ->inRandomOrder()
            ->take($number)
            ->get()
            ->sortBy('number');
    }
}
