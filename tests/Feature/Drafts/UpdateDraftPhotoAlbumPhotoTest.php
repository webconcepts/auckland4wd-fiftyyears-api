<?php

use App\Item;
use App\User;
use App\Photo;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UpdateDraftPhotoAlbumPhotoTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_update_a_photo_belonging_to_a_photo_album()
    {
        $album = factory(Item::class)->states('album', 'draft')->create();
        $photo = factory(Photo::class)->state('not-uploaded')->create([
            'item_id' => $album->id,
            'description' => 'Original description',
            'number' => 1
        ]);

        Auth::login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId(), [
            'description' => 'This is a new description',
            'number' => 2,
            'uploaded' => true
        ]);

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                'id',
                'number',
                'uploaded',
                'description',
            ]
        ]);
        $this->seeJson([
            'id' => $photo->obfuscatedId(),
            'description' => 'This is a new description',
            'number' => 2,
            'uploaded' => true
        ]);

        tap($photo->fresh(), function ($photo) {
            $this->assertEquals('This is a new description', $photo->description);
            $this->assertEquals(2, $photo->number);
            $this->assertTrue($photo->isUploaded());
        });
    }

    /** @test **/
    public function one_of_description_number_or_uploaded_must_be_given()
    {
        $photo = factory(Photo::class)->state('not-uploaded')->create();
        $album = $photo->item;

        Auth::login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId(), []);

        $this->seeStatusCode(400);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId(), [
            'description' => 'This is a new description'
        ]);

        $this->seeStatusCode(200);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId(), [
            'number' => 2
        ]);

        $this->seeStatusCode(200);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId(), [
            'uploaded' => true
        ]);

        $this->seeStatusCode(200);
    }

    /** @test **/
    public function number_must_be_an_integer()
    {
        $photo = factory(Photo::class)->state('not-uploaded')->create();
        $album = $photo->item;

        Auth::login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId(), [
            'number' => 'a'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('number');
    }

    /** @test **/
    public function uploaded_must_be_an_boolean()
    {
        $photo = factory(Photo::class)->state('not-uploaded')->create();
        $album = $photo->item;

        Auth::login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId(), [
            'uploaded' => 'yes'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('uploaded');
    }

    /** @test **/
    public function cannot_update_a_photo_record_for_a_published_album()
    {
        $album = factory(Item::class)->states('album', 'published')->create();
        $photo = factory(Photo::class)->state('not-uploaded')->create([
            'item_id' => $album->id
        ]);

        Auth::login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId(), [
            'description' => 'This is a new description'
        ]);

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function cannot_update_a_photo_record_for_somebody_elses_album()
    {
        $photo = factory(Photo::class)->state('not-uploaded')->create();
        $album = $photo->item;

        Auth::login(factory(User::class)->create()); // log in as someone else

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId(), [
            'description' => 'This is a new description'
        ]);

        $this->seeStatusCode(403);
    }

    /** @test **/
    public function can_update_a_photo_record_for_somebody_elses_album_as_an_editor()
    {
        $photo = factory(Photo::class)->state('not-uploaded')->create();
        $album = $photo->item;

        Auth::login(factory(User::class)->states('editor')->create()); // log in as someone else

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId(), [
            'description' => 'This is a new description'
        ]);

        $this->seeStatusCode(200);
    }

    /** @test **/
    public function cannot_update_a_removed_photo_record()
    {
        $album = factory(Item::class)->states('album', 'draft')->create();
        $photo = factory(Photo::class)->state('not-uploaded')->create([
            'item_id' => $album->id
        ]);

        $photo->remove();

        Auth::login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId().'/photos/'.$photo->obfuscatedId(), [
            'description' => 'This is a new description'
        ]);

        $this->seeStatusCode(404);
    }
}
