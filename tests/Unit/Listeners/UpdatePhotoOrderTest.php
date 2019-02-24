<?php

use App\Photo;
use App\PhotoAlbum;
use App\Events\PhotoSaved;
use App\Listeners\UpdatePhotoOrder;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UpdatePhotoOrderTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function increments_number_for_all_photos_in_same_album_with_equal_or_higher_number()
    {
        $album = factory(PhotoAlbum::class)->state('draft')->create();
        $photo1 = factory(Photo::class)->create(['photo_album_id' => $album->id, 'number' => 1]);
        $photo2 = factory(Photo::class)->create(['photo_album_id' => $album->id, 'number' => 2]);
        $photo3 = factory(Photo::class)->create(['photo_album_id' => $album->id, 'number' => 3]);
        $photo4 = factory(Photo::class)->create(['photo_album_id' => $album->id, 'number' => 4]);
        $photo5 = factory(Photo::class)->create(['photo_album_id' => $album->id, 'number' => 5]);

        $photo4->number = 2;
        $photo4->save();

        $this->assertEquals(1, $photo1->fresh()->number);
        $this->assertEquals(2, $photo4->fresh()->number);
        $this->assertEquals(3, $photo2->fresh()->number);
        $this->assertEquals(4, $photo3->fresh()->number);
        $this->assertEquals(6, $photo5->fresh()->number);

        $photo1->number = 4;
        $photo1->save();

        $this->assertEquals(2, $photo4->fresh()->number);
        $this->assertEquals(3, $photo2->fresh()->number);
        $this->assertEquals(4, $photo1->fresh()->number);
        $this->assertEquals(5, $photo3->fresh()->number);
        $this->assertEquals(7, $photo5->fresh()->number);
    }

    /** @test **/
    public function does_not_increment_other_numbers_if_number_was_not_changed()
    {
        $album = factory(PhotoAlbum::class)->state('draft')->create();
        $photo1 = factory(Photo::class)->create(['photo_album_id' => $album->id, 'number' => 1]);
        $photo2 = factory(Photo::class)->create(['photo_album_id' => $album->id, 'number' => 2]);
        $photo3 = factory(Photo::class)->create(['photo_album_id' => $album->id, 'number' => 3]);
        $photo4 = factory(Photo::class)->create(['photo_album_id' => $album->id, 'number' => 4]);
        $photo5 = factory(Photo::class)->create(['photo_album_id' => $album->id, 'number' => 5]);

        $photo4->number = 4;
        $photo4->save();

        $this->assertEquals(1, $photo1->fresh()->number);
        $this->assertEquals(2, $photo2->fresh()->number);
        $this->assertEquals(3, $photo3->fresh()->number);
        $this->assertEquals(4, $photo4->fresh()->number);
        $this->assertEquals(5, $photo5->fresh()->number);
    }
}
