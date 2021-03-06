<?php

use App\Item;
use App\Photo;
use Carbon\Carbon;
use App\IdObfuscator;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RetrievePhotoAlbumTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_retrieve_a_published_photo_album()
    {
        $coverPhoto = factory(Photo::class)->states('uploaded')->create();
        $album = factory(Item::class)->states('album', 'published')->create([
            'title' => 'Woodhill forest trip',
            'date' => Carbon::parse('November 12, 1995'),
            'approx_day' => 12,
            'approx_month' => 11,
            'approx_year' => 1995,
            'location' => 'Woodhill forest',
            'authorship' => 'John Smith',
            'description' => '<p>This trip was organised by Joe Blogs.</p><p>We had a very large turnout, with over 40 vehicles attending</p>',
            'cover_photo_id' => $coverPhoto->id,
        ]);
        $coverPhoto->update(['item_id' => $album->id]);

        $this->json('GET', '/photo-albums/'.$album->obfuscatedId());

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                'id', 'title', 'date', 'approx_day', 'approx_month', 'approx_year',
                'location', 'authorship', 'description', 'cover_photo_id'
            ]
        ]);
        $this->seeJson([
            'id' => $album->obfuscatedId(),
            'title' => 'Woodhill forest trip',
            'date' => '1995-11-12',
            'approx_day' => 12,
            'approx_month' => 11,
            'approx_year' => 1995,
            'location' => 'Woodhill forest',
            'authorship' => 'John Smith',
            'description' => '<p>This trip was organised by Joe Blogs.</p><p>We had a very large turnout, with over 40 vehicles attending</p>',
            'cover_photo_id' => $coverPhoto->obfuscatedId()
        ]);
    }

    /** @test **/
    public function cannot_retrieve_a_draft_photo_album()
    {
        $album = factory(Item::class)->states('album', 'draft')->create();

        $this->json('GET', '/photo-albums/'.$album->obfuscatedId());

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function can_retrieve_a_list_of_only_published_photo_albums()
    {
        $this->withoutExceptionHandling();

        $albumA = factory(Item::class)->states('album', 'published')->create();
        $draft = factory(Item::class)->states('album', 'draft')->create();
        $albumB = factory(Item::class)->states('album', 'published')->create();
        $albumC = factory(Item::class)->states('album', 'published')->create();

        $this->json('GET', '/photo-albums');
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
