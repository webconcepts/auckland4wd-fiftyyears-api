<?php

namespace App\Http\Controllers\Drafts;

use App\User;
use App\Item;
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

        $album = Item::create([
            'type' => Item::PHOTO_ALBUM,
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

        $validData = collect($this->validDataOrAbort($request, [
            'title' => 'nullable',
            'date' => 'nullable|date_format:"Y-m-d"',
            'approx_day' => 'nullable|integer|between:1,31',
            'approx_month' => 'nullable|integer|between:1,12',
            'approx_year' => 'nullable|integer|between:1969,2019',
            'location' => 'nullable',
            'authorship' => 'nullable',
            'description' => 'nullable',
        ]));

        $album->update($validData->all());

        $givenApproximateDate = $validData
            ->only(['approx_day', 'approx_month', 'approx_year'])
            ->count();

        if (!Auth::user()->isEditor() && !$album->wasChanged('date') && $givenApproximateDate) {
            $album->setDateFromApproximateDate()
                ->save();
        }

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
        $album = Item::photoAlbum()->draft()
            ->findOrFail(Item::actualId($obfuscatedId));

        $this->authorize('edit', $album);

        return $album;
    }
}
