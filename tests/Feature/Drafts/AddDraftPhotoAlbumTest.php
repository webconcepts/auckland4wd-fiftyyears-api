<?php

use App\User;
use App\PhotoAlbum;
use App\IdObfuscator;
use Tymon\JWTAuth\JWT;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AddDraftPhotoAlbumTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_create_a_valid_photo_album()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create([
            'email' => 'jane@blogs.com'
        ]);
        app('auth')->login($user);

        $this->json('POST', '/drafts/photo-albums', [
            'title' => 'Woodhill forest trip'
        ]);

        $this->seeStatusCode(201);
        $this->seeJsonStructure([
            'data' => [
                'id', 'title', 'date', 'location', 'photographer', 'description'
            ]
        ]);

        tap(PhotoAlbum::first(), function ($album) use ($user) {
            $this->seeHeader('Location', url('/drafts/photo-albums/'.$album->obfuscatedId()));

            $this->seeJson([
                'id' => $album->obfuscatedId(),
                'title' => 'Woodhill forest trip',
                'date' => null,
                'location' => null,
                'photographer' => null,
                'description' => null
            ]);

            $this->assertFalse($album->isPublished());
            $this->assertEquals('Woodhill forest trip', $album->title);
            $this->assertEquals($user->id, $album->user->id);
            $this->assertEquals('jane@blogs.com', $album->user->email);
        });
    }

    /** @test **/
    public function guest_cannot_create_a_photo_album()
    {
        $this->json('POST', '/drafts/photo-albums', [
            'title' => 'Woodhill forest trip',
            'user' => (object) [
                'name' => 'Joe Blogs',
                'email' => 'joe@blogs.com'
            ]
        ]);

        $this->seeStatusCode(401);

        $this->assertEquals(0, PhotoAlbum::count());
    }

    /** @test **/
    public function title_is_required()
    {
        app('auth')->login(factory(User::class)->create());

        $this->json('POST', '/drafts/photo-albums', [
            'title' => ''
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('title');
    }
}
