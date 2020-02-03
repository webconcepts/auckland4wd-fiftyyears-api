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
            'seed' => 'integer|min:1|nullable',
            'number' => 'integer|min:1|nullable',
            'from_year' => 'integer|min:1969|max:2019|nullable',
        ]);

        $album = $this->getAlbum(
            $offset = $request->input('offset', 1),
            $seed = $request->input('seed', null),
            $fromYear = $request->input('from_year', null)
        );

        $photos = $this->getPhotos($album, $request->input('number', 7));

        return [
            'data' => $album,
            'photos' => $photos->values(),
            'offset' => $offset,
            'seed' => $seed,
            'from_year' => $fromYear,
        ];
    }

    /**
     * Get an album, either randomly, or at a given offset
     *
     * @param int $offset
     * @param int $seed for random ordering
     * @param int $fromYear
     * @return Item
     */
    protected function getAlbum($offset = 1, $seed = null, $fromYear = null)
    {
        $albums = Item::published()->where('type', Item::PHOTO_ALBUM);

        if ($fromYear) {
            $albums = $albums->where('date', '>=', $fromYear.'-01-01');
        }

        if ($seed) {
            $ordered = $albums->inRandomOrder($seed);
        } else {
            $ordered = $albums->orderBy('date');
        }

        return $ordered->offset((int) $offset - 1)->firstOrFail();
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
            ->uploaded()
            ->orderBy('likes', 'desc')
            ->inRandomOrder()
            ->take($number)
            ->get()
            ->sortBy('number');
    }
}
