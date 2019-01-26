<?php

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

        $this->json('DELETE', '/drafts/photo-albums/'.$album->obfuscatedId());

        $this->seeStatusCode(200);
        $this->assertTrue($album->fresh()->isRemoved());
    }

    /** @test **/
    public function cannot_remove_a_published_album()
    {
        $album = factory(PhotoAlbum::class)->states('published')->create();

        $this->json('DELETE', '/drafts/photo-albums/'.$album->obfuscatedId());

        $this->seeStatusCode(404);
        $this->assertFalse($album->fresh()->isRemoved());
    }

    /** @test **/
    public function cannot_remove_an_already_removed_album()
    {
        $album = factory(PhotoAlbum::class)->states('removed')->create();

        $this->json('DELETE', '/drafts/photo-albums/'.$album->obfuscatedId());

        $this->seeStatusCode(404);
    }
}
