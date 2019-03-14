<?php

namespace App\Http\Controllers\Drafts;

use App\Item;
use App\Video\VideoInfo;
use Illuminate\Http\Request;
use App\Exceptions\InvalidVideoTypeException;

class VideoController extends ItemController
{
    protected $type = Item::VIDEO;

    protected function attributesToUpdateValidationRules()
    {
        return array_merge(parent::attributesToUpdateValidationRules(), [
            'location' => 'nullable',
            'authorship' => 'nullable',
            'video_url' => 'nullable|URL',
        ]);
    }

    /**
     * Set the video attributes
     */
    protected function onAfterUpdate(Item $item, Request $request)
    {
        if ($item->wasChanged('video_url')) {
            try {
                $item->setVideoDetailsFromUrl();
            } catch (InvalidVideoTypeException $e) {
                abort(422, 'Invalid video type');
            }
        }
    }
}
