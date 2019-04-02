<?php

use App\Item;
use App\Photo;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class LikePhotoAlbumPhotoTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_add_a_like_to_a_photo_in_a_published_album()
    {
        $this->withoutExceptionHandling();

        $album = factory(Item::class)->states('album', 'published')->create();
        $photo = factory(Photo::class)->create([
            'item_id' => $album->id,
            'number' => 24,
            'uploaded' => true,
            'description' => 'This is an example description',
            'likes' => 0
        ]);

        $this->json('POST', '/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId().'/likes', [
            'likes' => 1
        ]);

        $this->seeStatusCode(200);

        $this->seeJsonStructure(['data' => ['likes']]);
        $this->assertEquals(1, $photo->fresh()->likes);
    }

    /** @test **/
    public function can_add_multiple_likes_to_a_photo_in_a_published_album()
    {
        $album = factory(Item::class)->states('album', 'published')->create();
        $photo = factory(Photo::class)->create([
            'item_id' => $album->id,
            'likes' => 5
        ]);

        $this->json('POST', '/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId().'/likes', [
            'likes' => 10
        ]);

        $this->seeStatusCode(200);

        $this->assertEquals(15, $photo->fresh()->likes);
    }

    /** @test **/
    public function likes_is_required()
    {
        $album = factory(Item::class)->states('album', 'published')->create();
        $photo = factory(Photo::class)->create([
            'item_id' => $album->id,
            'likes' => 5
        ]);

        $this->json('POST', '/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId().'/likes', []);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('likes');
        $this->assertEquals(5, $photo->fresh()->likes);
    }

    /** @test **/
    public function cannot_add_negative_likes()
    {
        $album = factory(Item::class)->states('album', 'published')->create();
        $photo = factory(Photo::class)->create([
            'item_id' => $album->id,
            'likes' => 5
        ]);

        $this->json('POST', '/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId().'/likes', [
            'likes' => -3
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('likes');
        $this->assertEquals(5, $photo->fresh()->likes);
    }

    /** @test **/
    public function cannot_add_likes_to_a_photo_in_a_draft_album()
    {
        $album = factory(Item::class)->states('album', 'draft')->create();
        $photo = factory(Photo::class)->create(['item_id' => $album->id]);

        $this->json('POST', '/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId().'/likes', [
            'likes' => 1
        ]);

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function cannot_add_likes_to_a_photo_in_a_removed_album()
    {
        $album = factory(Item::class)->states('album', 'removed')->create();
        $photo = factory(Photo::class)->create(['item_id' => $album->id]);

        $this->json('POST', '/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId().'/likes', [
            'likes' => 1
        ]);

        $this->seeStatusCode(404);
    }
}
