<?php

namespace App\Http\Controllers\Drafts;

use App\Photo;
use App\PhotoAlbum;
use App\S3DirectUpload;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PhotoAlbumPhotoController extends Controller
{
    /**
     * Create a new photo record for this album, and return the AWS request data
     * needed to upload the file direct to S3
     */
    public function store($obfuscatedAlbumId, Request $request, S3DirectUpload $upload)
    {
        $this->validate($request, [
            'filename' => 'required',
            'type' => ['required', Rule::in(Photo::types())],
            'number' => 'required|integer',
        ]);

        $album = PhotoAlbum::draft()
            ->findOrFail(PhotoAlbum::actualId($obfuscatedAlbumId));

        $this->authorize('update', $album);

        $photo = $album->photos()->create([
            'uploaded_by_id' => Auth::id(),
            'original_filename' => $request->input('filename'),
            'type' => $request->input('type'),
            'number' => $request->input('number')
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
}
