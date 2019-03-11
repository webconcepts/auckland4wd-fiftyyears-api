<?php

use App\Item;
use App\Photo;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class PhotoTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function photo_in_a_draft_photo_album_can_be_removed()
    {
        $album = factory(Item::class)->states('album', 'draft')->create();
        $photo = factory(Photo::class)->create(['item_id' => $album->id]);

        $this->assertTrue($album->isDraft());
        $this->assertFalse($photo->isRemoved());

        $photo->remove();

        $this->assertTrue($photo->isRemoved());
    }

    /** @test **/
    public function photo_in_a_published_photo_album_cannot_be_removed()
    {
        $album = factory(Item::class)->states('album', 'published')->create();
        $photo = factory(Photo::class)->create(['item_id' => $album->id]);

        $this->assertFalse($photo->isRemoved());

        $photo->remove();

        $this->assertFalse($photo->isRemoved());
    }
}
