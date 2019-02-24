<?php

use App\User;
use Carbon\Carbon;
use App\PhotoAlbum;
use App\IdObfuscator;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RetrieveDraftPhotoAlbumTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_retrieve_a_draft_photo_album()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create([
            'title' => 'Woodhill forest trip',
            'date' => Carbon::parse('November 12, 1995'),
            'location' => 'Woodhill forest',
            'photographer' => 'John Smith',
            'description' => '<p>This trip was organised by Joe Blogs.</p><p>We had a very large turnout, with over 40 vehicles attending</p>',
        ]);

        app('auth')->login($album->user);

        $this->json('GET', '/drafts/photo-albums/'.$album->obfuscatedId());

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                'id', 'title', 'date', 'location', 'photographer', 'description'
            ]
        ]);
        $this->seeJson([
            'id' => $album->obfuscatedId(),
            'title' => 'Woodhill forest trip',
            'date' => '1995-11-12',
            'location' => 'Woodhill forest',
            'photographer' => 'John Smith',
            'description' => '<p>This trip was organised by Joe Blogs.</p><p>We had a very large turnout, with over 40 vehicles attending</p>',
        ]);
    }

    /** @test **/
    public function cannot_retrieve_a_published_photo_album()
    {
        $album = factory(PhotoAlbum::class)->states('published')->create();

        app('auth')->login($album->user);

        $this->json('GET', '/drafts/photo-albums/'.$album->obfuscatedId());

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function cannot_retrieve_someone_elses_draft_photo_album()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create();

        // log in as someone else, not owner of this album
        app('auth')->login(factory(User::class)->create());

        $this->json('GET', '/drafts/photo-albums/'.$album->obfuscatedId());

        $this->seeStatusCode(403);
    }

    /** @test **/
    public function can_retrieve_a_list_of_only_draft_photo_albums()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $albumA = factory(PhotoAlbum::class)->states('draft')->create(['user_id' => $user->id]);
        $published = factory(PhotoAlbum::class)->states('published')->create(['user_id' => $user->id]);
        $albumB = factory(PhotoAlbum::class)->states('draft')->create(['user_id' => $user->id]);
        $otherUsersAlbum = factory(PhotoAlbum::class)->states('draft')->create(['user_id' => $otherUser->id]);
        $albumC = factory(PhotoAlbum::class)->states('draft')->create(['user_id' => $user->id]);

        app('auth')->login($user);

        $this->json('GET', '/drafts/photo-albums');
        $content = json_decode($this->response->getContent());

        $this->seeStatusCode(200);
        $this->seeJson();

        $this->assertCollectionEquals([
            $albumA,
            $albumB,
            $albumC
        ], $this->responseData('data'));
    }
}
