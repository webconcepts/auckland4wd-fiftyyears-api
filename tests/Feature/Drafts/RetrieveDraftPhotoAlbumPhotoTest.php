<?php

use App\Item;
use App\User;
use App\Photo;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RetrieveDraftPhotoAlbumPhotoTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_retrieve_a_photo_for_a_draft_photo_album()
    {
        $this->withoutExceptionHandling();

        $album = factory(Item::class)->states('album', 'draft')->create();
        $photo = factory(Photo::class)->create([
            'item_id' => $album->id,
            'number' => 24,
            'uploaded' => true,
            'description' => 'This is an example description',
        ]);

        factory(Photo::class)->states('uploaded')->create(['item_id' => $album->id, 'number' => 27]);
        factory(Photo::class)->states('uploaded')->create(['item_id' => $album->id, 'number' => 29]);
        $next = factory(Photo::class)->states('uploaded')->create(['item_id' => $album->id, 'number' => 26]);
        $previous = factory(Photo::class)->states('uploaded')->create(['item_id' => $album->id, 'number' => 23]);
        factory(Photo::class)->states('uploaded')->create(['item_id' => $album->id, 'number' => 22]);
        factory(Photo::class)->states('uploaded')->create(['item_id' => $album->id, 'number' => 21]);

        app('auth')->login($album->user);

        $this->json('GET', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId());

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                'id', 'number', 'uploaded', 'description'
            ],
            'next',
            'previous'
        ]);
        $this->seeJson([
            'id' => $photo->obfuscatedId(),
            'number' => 24,
            'uploaded' => true,
            'description' => 'This is an example description',
        ]);
        $this->seeJson(['next' => $next->obfuscatedId()]);
        $this->seeJson(['previous' => $previous->obfuscatedId()]);
    }

    /** @test **/
    public function cannot_retrieve_a_photo_in_a_published_photo_album()
    {
        $album = factory(Item::class)->states('album', 'published')->create();
        $photo = factory(Photo::class)->create(['item_id' => $album->id]);

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

        $this->json('GET', '/drafts/photo-albums/'.$photo->item->obfuscatedId().'/photos/'.$photo->obfuscatedId());

        $this->seeStatusCode(403);
    }

    /** @test **/
    public function can_retrieve_a_photo_in_someone_elses_draft_photo_album_as_an_editor()
    {
        $photo = factory(Photo::class)->create();

        // log in as someone else, not owner of this album
        app('auth')->login(factory(User::class)->states('editor')->create());

        $this->json('GET', '/drafts/photo-albums/'.$photo->item->obfuscatedId().'/photos/'.$photo->obfuscatedId());

        $this->seeStatusCode(200);
    }

    /** @test **/
    public function can_retrieve_a_list_of_photos_in_a_draft_photo_album_in_number_order()
    {
        $album = factory(Item::class)->states('album', 'draft')->create();

        $photoA = factory(Photo::class)->states('uploaded')->create(['number' => 1, 'item_id' => $album->id]);
        $photoB = factory(Photo::class)->states('uploaded')->create(['number' => 3, 'item_id' => $album->id]);
        $photoC = factory(Photo::class)->states('removed')->create(['number' => 4, 'item_id' => $album->id]);
        $photoD = factory(Photo::class)->states('uploaded')->create(['number' => 5, 'item_id' => $album->id]);
        $photoE = factory(Photo::class)->states('not-uploaded')->create(['number' => 6, 'item_id' => $album->id]);
        $photoF = factory(Photo::class)->states('uploaded')->create(['number' => 2, 'item_id' => $album->id]);

        app('auth')->login($album->user);

        $this->json('GET', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos');

        $this->seeStatusCode(200);
        $this->seeJson();

        $this->assertCollectionEquals([
            $photoA,
            $photoF,
            $photoB,
            $photoD,
        ], $this->responseData('data'));
    }
}
