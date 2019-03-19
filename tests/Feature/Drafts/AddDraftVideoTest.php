<?php

use App\User;
use App\Item;
use App\IdObfuscator;
use Tymon\JWTAuth\JWT;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AddDraftVideoTest extends TestCase
{
    use DatabaseMigrations, AddDraftItemContractTests;

    protected $itemUrlPath = 'videos';

    /** @test **/
    public function can_create_a_valid_photo_album()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create([
            'email' => 'jane@blogs.com'
        ]);
        app('auth')->login($user);

        $this->json('POST', '/drafts/videos', [
            'title' => 'Waiuku trip video'
        ]);

        $this->seeStatusCode(201);
        $this->seeJsonStructure([
            'data' => [
                'id', 'title', 'date', 'location', 'authorship', 'description', 'video_url', 'video_type', 'cover_photo_id'
            ]
        ]);

        tap(Item::first(), function ($video) use ($user) {
            $this->seeHeader('Location', url('/drafts/videos/'.$video->obfuscatedId()));

            $this->seeJson([
                'id' => $video->obfuscatedId(),
                'title' => 'Waiuku trip video',
                'date' => null,
                'location' => null,
                'authorship' => null,
                'description' => null,
                'video_url' => null,
                'video_type' => null,
                'cover_photo_id' => null,
            ]);

            $this->assertEquals(Item::VIDEO, $video->type);
            $this->assertFalse($video->isPublished());
            $this->assertEquals('Waiuku trip video', $video->title);
            $this->assertEquals($user->id, $video->user->id);
            $this->assertEquals('jane@blogs.com', $video->user->email);
        });
    }
}
