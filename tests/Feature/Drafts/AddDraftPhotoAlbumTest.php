<?php

use App\User;
use App\Item;
use App\IdObfuscator;
use Tymon\JWTAuth\JWT;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AddDraftPhotoAlbumTest extends TestCase
{
    use DatabaseMigrations, AddDraftItemContractTests;

    protected $itemUrlPath = 'photo-albums';

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
                'id', 'title', 'date', 'location', 'authorship', 'description'
            ]
        ]);

        tap(Item::first(), function ($album) use ($user) {
            $this->seeHeader('Location', url('/drafts/photo-albums/'.$album->obfuscatedId()));

            $this->seeJson([
                'id' => $album->obfuscatedId(),
                'title' => 'Woodhill forest trip',
                'date' => null,
                'location' => null,
                'authorship' => null,
                'description' => null
            ]);

            $this->assertFalse($album->isPublished());
            $this->assertEquals('Woodhill forest trip', $album->title);
            $this->assertEquals($user->id, $album->user->id);
            $this->assertEquals('jane@blogs.com', $album->user->email);
        });
    }
}
