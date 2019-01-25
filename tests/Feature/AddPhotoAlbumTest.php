<?php

use App\User;
use App\PhotoAlbum;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AddPhotoAlbumTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function guest_can_create_a_valid_photo_album()
    {
        $this->withoutExceptionHandling();

        $this->json('POST', '/photoalbums', [
            'title' => 'Woodhill forest trip',
            'user' => (object) [
                'name' => 'Joe Blogs',
                'email' => 'joe@blogs.com'
            ]
        ]);

        $this->seeStatusCode(201);
        $this->seeJsonStructure([
            'data' => [
                'title', 'date', 'location', 'photographer', 'description'
            ]
        ]);
        $this->seeJson([
            'title' => 'Woodhill forest trip',
        ]);

        tap(PhotoAlbum::first(), function ($album) {
            $this->seeHeader('Location', url('/photoalbums/'.$album->id));

            $this->assertFalse($album->isPublished());
            $this->assertEquals('Woodhill forest trip', $album->title);
            $this->assertEquals('joe@blogs.com', $album->user->email);
            $this->assertEquals('Joe Blogs', $album->user->name);
        });
    }

    /** @test **/
    public function existing_user_can_create_a_valid_photo_album()
    {
        $user = factory(User::class)->create([
            'email' => 'jane@blogs.com'
        ]);

        $this->json('POST', '/photoalbums', [
            'title' => 'Woodhill forest trip',
            'user' => (object) [
                'email' => 'jane@blogs.com'
            ]
        ]);

        tap(PhotoAlbum::first(), function ($album) use ($user) {
            $this->assertEquals($user->id, $album->user_id);
            $this->assertEquals($user->email, $album->user->email);
        });

        $this->seeStatusCode(201);
    }

    /** @test **/
    public function title_is_required()
    {
        $this->json('POST', '/photoalbums', [
            'title' => '',
            'user' => (object) [
                'name' => 'Joe Blogs',
                'email' => 'joe@blogs.com'
            ]
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('title');
    }

    /** @test **/
    public function user_email_is_required()
    {
        $this->json('POST', '/photoalbums', [
            'title' => 'Woodhill forest trip',
            'user' => (object) [
                'name' => 'Joe Blogs',
                'email' => ''
            ]
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('user.email');
    }
}
