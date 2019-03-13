<?php

use App\Item;
use App\User;
use App\Photo;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RemovePhotoAlbumCoverPhotoTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_remove_a_cover_photo_from_a_photo_album()
    {
        $photo = factory(Photo::class)->state('uploaded')->create();
        $album = factory(Item::class)->states('album', 'draft')->create([
            'cover_photo_id' => $photo->id,
        ]);
        $photo->update(['item_id' => $album->id]);

        Auth::login($album->user);

        $this->assertEquals($album->coverPhoto->id, $photo->id);

        $this->json('DELETE', '/drafts/photo-albums/'.$album->obfuscatedId().'/cover-photo');

        $this->seeStatusCode(200);

        tap($album->fresh(), function ($album) use ($photo) {
            $this->assertNull($album->cover_photo_id);
            $this->assertNull($album->coverPhoto);
        });

        // check photo hasnt been deleted
        $this->assertEquals($photo->id, $photo->fresh()->id);
    }

    /** @test **/
    public function cannot_remove_a_cover_photo_from_a_published_album()
    {
        $photo = factory(Photo::class)->state('uploaded')->create();
        $album = factory(Item::class)->states('album', 'published')->create([
            'cover_photo_id' => $photo->id,
        ]);
        $photo->update(['item_id' => $album->id]);

        Auth::login($album->user);

        $this->json('DELETE', '/drafts/photo-albums/'.$album->obfuscatedId().'/cover-photo');

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function cannot_remove_a_cover_photo_as_guest()
    {
        $photo = factory(Photo::class)->state('uploaded')->create();
        $album = factory(Item::class)->states('album', 'published')->create([
            'cover_photo_id' => $photo->id,
        ]);
        $photo->update(['item_id' => $album->id]);

        // not logged in

        $this->json('DELETE', '/drafts/photo-albums/'.$album->obfuscatedId().'/cover-photo');

        $this->seeStatusCode(401);
    }

    /** @test **/
    public function cannot_remove_a_cover_photo_from_someone_elses_album()
    {
        $photo = factory(Photo::class)->state('uploaded')->create();
        $album = factory(Item::class)->states('album', 'draft')->create([
            'cover_photo_id' => $photo->id,
        ]);
        $photo->update(['item_id' => $album->id]);

        Auth::login(factory(User::class)->create()); // logged in as someone else

        $this->json('DELETE', '/drafts/photo-albums/'.$album->obfuscatedId().'/cover-photo');

        $this->seeStatusCode(403);
    }

    /** @test **/
    public function can_remove_a_cover_photo_from_someone_elses_album_as_an_editor()
    {
        $photo = factory(Photo::class)->state('uploaded')->create();
        $album = factory(Item::class)->states('album', 'draft')->create([
            'cover_photo_id' => $photo->id,
        ]);
        $photo->update(['item_id' => $album->id]);

        Auth::login(factory(User::class)->state('editor')->create()); // logged in as an editor

        $this->json('DELETE', '/drafts/photo-albums/'.$album->obfuscatedId().'/cover-photo');

        $this->seeStatusCode(200);
    }
}
