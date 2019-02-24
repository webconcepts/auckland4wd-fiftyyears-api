<?php

use App\User;
use App\PhotoAlbum;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RemoveDraftPhotoAlbumTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_remove_a_draft_album()
    {
        $this->withoutExceptionHandling();

        $album = factory(PhotoAlbum::class)->states('draft')->create();
        app('auth')->login($album->user);

        $this->json('DELETE', '/drafts/photo-albums/'.$album->obfuscatedId());

        $this->seeStatusCode(200);
        $this->assertTrue($album->fresh()->isRemoved());
    }

    /** @test **/
    public function cannot_remove_a_published_album()
    {
        $album = factory(PhotoAlbum::class)->states('published')->create();
        app('auth')->login($album->user);

        $this->json('DELETE', '/drafts/photo-albums/'.$album->obfuscatedId());

        $this->seeStatusCode(404);
        $this->assertFalse($album->fresh()->isRemoved());
    }

    /** @test **/
    public function cannot_remove_an_already_removed_album()
    {
        $album = factory(PhotoAlbum::class)->states('removed')->create();
        app('auth')->login($album->user);

        $this->json('DELETE', '/drafts/photo-albums/'.$album->obfuscatedId());

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function cannot_remove_someone_elses_album()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create();
        app('auth')->login(factory(User::class)->create());

        $this->json('DELETE', '/drafts/photo-albums/'.$album->obfuscatedId());

        $this->seeStatusCode(403);
    }

    /** @test **/
    public function cannot_remove_an_album_as_a_guest()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create();

        $this->json('DELETE', '/drafts/photo-albums/'.$album->obfuscatedId());

        $this->seeStatusCode(401);
    }

    /** @test **/
    public function can_remove_someone_elses_album_as_an_editor()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create();
        app('auth')->login(factory(User::class)->states('editor')->create());

        $this->json('DELETE', '/drafts/photo-albums/'.$album->obfuscatedId());

        $this->seeStatusCode(200);
    }
}
