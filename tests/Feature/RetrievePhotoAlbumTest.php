<?php

use Carbon\Carbon;
use App\PhotoAlbum;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RetrievePhotoAlbumTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_retrieve_a_published_photo_album()
    {
        $album = factory(PhotoAlbum::class)->states('published')->create([
            'title' => 'Woodhill forest trip',
            'date' => Carbon::parse('November 12, 1995'),
            'location' => 'Woodhill forest',
            'photographer' => 'John Smith',
            'description' => '<p>This trip was organised by Joe Blogs.</p><p>We had a very large turnout, with over 40 vehicles attending</p>',
        ]);

        $this->json('GET', '/photoalbums/'.$album->id);
            
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                'title', 'date', 'location', 'photographer', 'description'
            ]
        ]);
        $this->seeJson([
            'title' => 'Woodhill forest trip',
            'date' => '1995-11-12',
            'location' => 'Woodhill forest',
            'photographer' => 'John Smith',
            'description' => '<p>This trip was organised by Joe Blogs.</p><p>We had a very large turnout, with over 40 vehicles attending</p>',
        ]);
    }

    /** @test **/
    public function cannot_retrieve_an_unpublished_photo_album()
    {
        $album = factory(PhotoAlbum::class)->states('unpublished')->create();

        $this->json('GET', '/photoalbums/'.$album->id);

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function can_retrieve_a_list_of_only_published_photo_albums()
    {
        $this->withoutExceptionHandling();

        $albumA = factory(PhotoAlbum::class)->states('published')->create();
        $unpublished = factory(PhotoAlbum::class)->states('unpublished')->create();
        $albumB = factory(PhotoAlbum::class)->states('published')->create();
        $albumC = factory(PhotoAlbum::class)->states('published')->create();

        $this->json('GET', '/photoalbums');
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
