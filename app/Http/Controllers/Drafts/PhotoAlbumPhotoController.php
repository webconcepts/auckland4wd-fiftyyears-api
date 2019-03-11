<?php

namespace App\Http\Controllers\Drafts;

use App\Item;
use App\Photo;
use App\S3DirectUpload;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PhotoAlbumPhotoController extends Controller
{
    /**
     * Get a list of photos for this draft photo album that are uploaded and
     * not removed.
     */
    public function index($obfuscatedAlbumId, Request $request)
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
     * Get a photo record for this draft photo album.
     */
    public function show($obfuscatedAlbumId, $obfuscatedId, Request $request)
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
     * Create a new photo record for this album, and return the AWS request data
     * needed to upload the file direct to S3
     */
    public function store($obfuscatedAlbumId, Request $request, S3DirectUpload $upload)
    {
        $album = $this->getAlbum($obfuscatedAlbumId);

        $this->validate($request, [
            'filename' => 'required',
            'type' => ['required', Rule::in(Photo::types())],
        ]);

        $photo = $album->photos()->create([
            'uploaded_by_id' => Auth::id(),
            'original_filename' => $request->input('filename'),
            'type' => $request->input('type'),
            'number' => $album->getNextAvailablePhotoNumber()
        ]);

        $upload
            ->setKey($photo->s3Key())
            ->setContentType($photo->type);

        return response([
            'data' => $photo,
            'upload' => [
                'url' => $upload->getUrl(),
                'data' => $upload->getRequestData(),
            ],
        ], 201);
    }

    /**
     * Update a photo's description, number, or uploaded status for this album
     */
    public function update($obfuscatedAlbumId, $obfuscatedId, Request $request)
    {
        $photo = $this->getAlbum($obfuscatedAlbumId)
            ->photos()
            ->findOrFail(Photo::actualId($obfuscatedId));

        $photo->update($this->validDataOrAbort($request, [
            'description' => 'nullable',
            'number' => 'integer|nullable',
            'uploaded' => 'boolean|nullable',
        ]));

        return ['data' => $photo];
    }

    /**
     * Remove a photo from this draft photo album
     */
    public function destroy($obfuscatedAlbumId, $obfuscatedId, Request $request)
    {
        $this->getAlbum($obfuscatedAlbumId)
            ->photos()
            ->findOrFail(Photo::actualId($obfuscatedId))
            ->remove();
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
