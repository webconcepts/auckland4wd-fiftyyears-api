<?php

use App\Item;
use App\Photo;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class PhotoTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function converting_to_an_array()
    {
        $photo = factory(Photo::class)->create([
            'id' => 123,
            'number' => 24,
            'description' => 'This is the description',
            'likes' => 5,
        ]);

        $array = $photo->toArray();

        $this->assertEquals($photo->obfuscatedId(), $array['id']);
        $this->assertEquals(24, $array['number']);
        $this->assertEquals('This is the description', $array['description']);
        $this->assertEquals(5, $array['likes']);
    }

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

    /** @test **/
    public function html_stripped_when_setting_description_value()
    {
        $photo = factory(Photo::class)->make();

        $photo->description = '<p>This is a description with <strong>tags</string> &amp; entities</p>';

        $this->assertEquals(
            'This is a description with tags & entities',
            $photo->description
        );
    }

    /** @test **/
    public function likes_default_to_zero()
    {
        $photo = new Photo();

        $this->assertEquals(0, $photo->likes);
    }
}
