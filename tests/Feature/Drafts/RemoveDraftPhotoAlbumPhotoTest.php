<?php

use App\Item;
use App\User;
use App\Photo;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RemoveDraftPhotoAlbumPhotoTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_remove_a_photo_from_a_draft_album()
    {
        $this->withoutExceptionHandling();

        $photo = factory(Photo::class)->create();
        $album = $photo->item;

        app('auth')->login($album->user);

        $this->json('DELETE', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId());

        $this->seeStatusCode(200);
        $this->assertTrue($photo->fresh()->isRemoved());
    }

    /** @test **/
    public function cannot_remove_a_photo_from_a_published_album()
    {
        $album = factory(Item::class)->states('album', 'published')->create();
        $photo = factory(Photo::class)->create(['item_id' => $album->id]);

        app('auth')->login($album->user);

        $this->json('DELETE', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId());

        $this->seeStatusCode(404);
        $this->assertFalse($photo->fresh()->isRemoved());
    }

    /** @test **/
    public function cannot_remove_an_already_removed_photo()
    {
        $photo = factory(Photo::class)->create();
        $album = $photo->item;

        app('auth')->login($album->user);

        $photo->remove();

        $this->json('DELETE', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId());

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function cannot_remove_a_photo_from_someone_elses_album()
    {
        $photo = factory(Photo::class)->create();

        app('auth')->login(factory(User::class)->create());

        $this->json('DELETE', '/drafts/photo-albums/'.$photo->item->obfuscatedId().'/photos/'.$photo->obfuscatedId());

        $this->seeStatusCode(403);
    }

    /** @test **/
    public function cannot_remove_a_photo_from_an_album_as_a_guest()
    {
        $photo = factory(Photo::class)->create();

        $this->json('DELETE', '/drafts/photo-albums/'.$photo->item->obfuscatedId().'/photos/'.$photo->obfuscatedId());

        $this->seeStatusCode(401);
    }

    /** @test **/
    public function can_remove_a_photo_from_someone_elses_album_as_an_editor()
    {
        $photo = factory(Photo::class)->create();

        app('auth')->login(factory(User::class)->states('editor')->create());

        $this->json('DELETE', '/drafts/photo-albums/'.$photo->item->obfuscatedId().'/photos/'.$photo->obfuscatedId());

        $this->seeStatusCode(200);
    }
}
