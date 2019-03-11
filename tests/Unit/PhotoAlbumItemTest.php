<?php

use App\Item;
use App\Photo;
use Carbon\Carbon;
use App\IdObfuscator;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class PhotoAlbumItemTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function get_next_available_number_for_photos_in_this_album()
    {
        $album = factory(Item::class)->states('album', 'draft')->create();
        factory(Photo::class)->create(['number' => 1, 'item_id' => $album->id]);
        factory(Photo::class)->create(['number' => 2, 'item_id' => $album->id]);
        factory(Photo::class)->create(['number' => 4, 'item_id' => $album->id]);

        $number = $album->getNextAvailablePhotoNumber();

        $this->assertEquals(5, $number);
    }

    /** @test **/
    public function get_next_available_number_for_photos_when_no_photos_exist()
    {
        $album = factory(Item::class)->states('album', 'draft')->create();

        $number = $album->getNextAvailablePhotoNumber();

        $this->assertEquals(1, $number);
    }
}
