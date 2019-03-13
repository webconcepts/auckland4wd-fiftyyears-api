<?php

namespace App\Http\Controllers\Drafts;

use App\Item;

class PhotoAlbumController extends ItemController
{
    protected $type = Item::PHOTO_ALBUM;

    protected function attributesToUpdateValidationRules()
    {
        return array_merge(parent::attributesToUpdateValidationRules(), [
            'location' => 'nullable',
            'authorship' => 'nullable'
        ]);
    }
}
