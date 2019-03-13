<?php

use App\Item;
use App\User;
use App\Photo;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AssignPhotoAlbumCoverPhotoTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_assign_photo_from_album_as_the_cover_photo()
    {
        $photo = factory(Photo::class)->state('uploaded')->create();
        $album = $photo->item;

        Auth::login($album->user);

        $this->assertNull($album->cover_photo_id);

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/cover-photo', [
            'id' => $photo->obfuscatedId()
        ]);

        $this->seeStatusCode(201);
        $this->seeJsonStructure([
            'data' => [
                'id',
                'number'
            ]
        ]);

        tap($album->fresh(), function ($album) use ($photo) {
            $this->assertEquals($photo->id, $album->coverPhoto->id);
            $this->assertEquals($photo->id, $album->cover_photo_id);
        });
    }

    /** @test **/
    public function id_is_required()
    {
        $photo = factory(Photo::class)->state('uploaded')->create();

        Auth::login($photo->item->user);

        $this->json('POST', '/drafts/photo-albums/'.$photo->item->obfuscatedId().'/cover-photo', []);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('id');
    }

    /** @test **/
    public function cannot_assign_photo_from_another_album()
    {
        $photo = factory(Photo::class)->state('uploaded')->create();
        $album = $photo->item;
        $otherAlbumPhoto = factory(Photo::class)->state('uploaded')->create();

        Auth::login($album->user);

        $this->assertNotSame($photo->item_id, $otherAlbumPhoto->item_id);

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/cover-photo', [
            'id' => $otherAlbumPhoto->obfuscatedId()
        ]);

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function cannot_assign_removed_photo()
    {
        $photo = factory(Photo::class)->state('removed')->create();

        Auth::login($photo->item->user);

        $this->json('POST', '/drafts/photo-albums/'.$photo->item->obfuscatedId().'/cover-photo', [
            'id' => $photo->obfuscatedId()
        ]);

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function cannot_assign_photo_that_isnt_uploaded()
    {
        $photo = factory(Photo::class)->state('not-uploaded')->create();

        Auth::login($photo->item->user);

        $this->json('POST', '/drafts/photo-albums/'.$photo->item->obfuscatedId().'/cover-photo', [
            'id' => $photo->obfuscatedId()
        ]);

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function cannot_assign_cover_photo_as_a_guest()
    {
        $photo = factory(Photo::class)->state('uploaded')->create();

        // not logged in

        $this->json('POST', '/drafts/photo-albums/'.$photo->item->obfuscatedId().'/cover-photo', [
            'id' => $photo->obfuscatedId()
        ]);

        $this->seeStatusCode(401);
    }

    /** @test **/
    public function cannot_assign_cover_photo_for_someone_elses_draft_album()
    {
        $photo = factory(Photo::class)->state('uploaded')->create();

        Auth::login(factory(User::class)->create()); // log in as someone else

        $this->json('POST', '/drafts/photo-albums/'.$photo->item->obfuscatedId().'/cover-photo', [
            'id' => $photo->obfuscatedId()
        ]);

        $this->seeStatusCode(403);
    }

    /** @test **/
    public function can_assign_cover_photo_for_someone_elses_draft_album_as_an_editor()
    {
        $photo = factory(Photo::class)->state('uploaded')->create();

        Auth::login(factory(User::class)->state('editor')->create()); // log in as editor

        $this->json('POST', '/drafts/photo-albums/'.$photo->item->obfuscatedId().'/cover-photo', [
            'id' => $photo->obfuscatedId()
        ]);

        $this->seeStatusCode(201);
    }

    /** @test **/
    public function cannot_assign_cover_photo_for_a_published_album()
    {
        $album = factory(Item::class)->states('album', 'published')->create();
        $photo = factory(Photo::class)->state('uploaded')->create([
            'item_id' => $album->id
        ]);

        Auth::login($album->user);

        $this->json('POST', '/drafts/photo-albums/'.$album->obfuscatedId().'/cover-photo', [
            'id' => $photo->obfuscatedId()
        ]);

        $this->seeStatusCode(404);
    }
}
