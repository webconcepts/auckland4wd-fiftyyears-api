<?php

use App\User;
use App\Photo;
use App\PhotoAlbum;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RetrieveDraftPhotoAlbumPhotoTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_retrieve_a_photo_for_a_draft_photo_album()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create();
        $photo = factory(Photo::class)->create([
            'photo_album_id' => $album->id,
            'number' => 24,
            'uploaded' => true,
            'description' => 'This is an example description',
        ]);

        app('auth')->login($album->user);

        $this->json('GET', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId());

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                'id', 'number', 'uploaded', 'description'
            ]
        ]);
        $this->seeJson([
            'id' => $photo->obfuscatedId(),
            'number' => 24,
            'uploaded' => true,
            'description' => 'This is an example description',
        ]);
    }

    /** @test **/
    public function cannot_retrieve_a_photo_in_a_published_photo_album()
    {
        $album = factory(PhotoAlbum::class)->states('published')->create();
        $photo = factory(Photo::class)->create(['photo_album_id' => $album->id]);

        app('auth')->login($album->user);

        $this->json('GET', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId());

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function cannot_retrieve_a_photo_in_someone_elses_draft_photo_album()
    {
        $photo = factory(Photo::class)->create();

        // log in as someone else, not owner of this album
        app('auth')->login(factory(User::class)->create());

        $this->json('GET', '/drafts/photo-albums/'.$photo->photoAlbum->obfuscatedId().'/photos/'.$photo->obfuscatedId());

        $this->seeStatusCode(403);
    }

    /** @test **/
    public function can_retrieve_a_photo_in_someone_elses_draft_photo_album_as_an_editor()
    {
        $photo = factory(Photo::class)->create();

        // log in as someone else, not owner of this album
        app('auth')->login(factory(User::class)->states('editor')->create());

        $this->json('GET', '/drafts/photo-albums/'.$photo->photoAlbum->obfuscatedId().'/photos/'.$photo->obfuscatedId());

        $this->seeStatusCode(200);
    }

    /** @test **/
    public function can_retrieve_a_list_of_photos_in_a_draft_photo_album()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create();

        $photoA = factory(Photo::class)->states('uploaded')->create(['photo_album_id' => $album->id]);
        $photoB = factory(Photo::class)->states('uploaded')->create(['photo_album_id' => $album->id]);
        $photoC = factory(Photo::class)->states('removed')->create(['photo_album_id' => $album->id]);
        $photoD = factory(Photo::class)->states('uploaded')->create(['photo_album_id' => $album->id]);
        $photoE = factory(Photo::class)->states('not-uploaded')->create(['photo_album_id' => $album->id]);
        $photoF = factory(Photo::class)->states('uploaded')->create(['photo_album_id' => $album->id]);

        app('auth')->login($album->user);

        $this->json('GET', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos');

        $this->seeStatusCode(200);
        $this->seeJson();

        $this->assertCollectionEquals([
            $photoA,
            $photoB,
            $photoD,
            $photoF
        ], $this->responseData('data'));
    }
}
