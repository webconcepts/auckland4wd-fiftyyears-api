<?php

namespace App\Listeners;

use App\Photo;
use App\Events\PhotoSaved;
use Illuminate\Support\Facades\DB;

class UpdatePhotoOrder
{
    public function handle(PhotoSaved $event)
    {
        if ($event->photo->wasChanged('number')) {
            $this->incrementOtherEqualOrHigherNumbers($event->photo);
        }
    }

    protected function incrementOtherEqualOrHigherNumbers(Photo $photo)
    {
        return DB::table($photo->getTable())
            ->where('id', '<>', $photo->id)
            ->where('item_id', $photo->item_id)
            ->where('number', '>=', $photo->number)
            ->increment('number');
    }
}
