<?php

namespace App\Listeners;

use App\Photo;
use App\Events\PhotoSaved;
use Illuminate\Support\Facades\DB;

class UpdatePhotoOrder
{
    public function handle(PhotoSaved $event)
    {
        if ($this->updateRequired($event->photo)) {
            $this->incrementOtherEqualOrHigherNumbers($event->photo);
        }
    }

    protected function updateRequired(Photo $photo)
    {
        return ($photo->wasRecentlyCreated && $this->duplicateNumberExists($photo))
            || (!$photo->wasRecentlyCreated && $photo->wasChanged('number'));
    }

    protected function duplicateNumberExists(Photo $photo)
    {
        return $this->getOtherAlbumPhotos($photo)
            ->where('number', $photo->number)
            ->count();
    }

    protected function incrementOtherEqualOrHigherNumbers(Photo $photo)
    {
        return $this->getOtherAlbumPhotos($photo)
            ->where('number', '>=', $photo->number)
            ->increment('number');
    }

    protected function getOtherAlbumPhotos(Photo $photo)
    {
        return DB::table($photo->getTable())
            ->where('id', '<>', $photo->id)
            ->where('item_id', $photo->item_id);
    }
}
