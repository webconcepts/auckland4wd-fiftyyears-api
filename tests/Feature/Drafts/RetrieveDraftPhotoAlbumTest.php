<?php

use App\Item;
use App\User;
use App\Photo;
use Carbon\Carbon;
use App\IdObfuscator;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RetrieveDraftPhotoAlbumTest extends TestCase
{
    use DatabaseMigrations, RetrieveDraftItemContractTests;

    protected $itemState = 'album';

    protected $itemUrlPath = 'photo-albums';

    /** @test **/
    public function can_retrieve_a_draft_photo_album()
    {
        $coverPhoto = factory(Photo::class)->states('uploaded')->create();
        $album = factory(Item::class)->states('album', 'draft')->create([
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

        app('auth')->login($album->user);

        $this->json('GET', '/drafts/photo-albums/'.$album->obfuscatedId());

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                'id', 'title', 'date', 'approx_day', 'approx_month', 'approx_year', 'location', 'authorship', 'description', 'cover_photo_id'
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
}
