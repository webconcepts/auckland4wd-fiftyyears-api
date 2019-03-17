<?php

use App\Item;
use App\User;
use App\Photo;
use Carbon\Carbon;
use App\IdObfuscator;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RetrieveDraftVideoTest extends TestCase
{
    use DatabaseMigrations, RetrieveDraftItemContractTests;

    protected $itemState = 'video';

    protected $itemUrlPath = 'videos';

    /** @test **/
    public function can_retrieve_a_draft_video()
    {
        $coverPhoto = factory(Photo::class)->states('uploaded')->create();
        $video = factory(Item::class)->states('video', 'draft')->create([
            'title' => 'Woodhill forest trip',
            'date' => Carbon::parse('November 12, 1995'),
            'approx_day' => 12,
            'approx_month' => 11,
            'approx_year' => 1995,
            'location' => 'Woodhill forest',
            'authorship' => 'John Smith',
            'description' => '<p>This trip was organised by Joe Blogs.</p><p>We had a very large turnout, with over 40 vehicles attending</p>',
            'video_url' => 'https://www.youtube.com/watch?v=3kjhd92387di',
            'video_type' => 'youtube',
            'video_id' => '3kjhd92387di',
            'cover_photo_id' => $coverPhoto->id,
        ]);

        app('auth')->login($video->user);

        $this->json('GET', '/drafts/videos/'.$video->obfuscatedId());

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                'id', 'title', 'date', 'approx_day', 'approx_month', 'approx_year',
                'location', 'authorship', 'description', 'video_url', 'video_type', 'video_id', 'cover_photo_id'
            ]
        ]);
        $this->seeJson([
            'id' => $video->obfuscatedId(),
            'title' => 'Woodhill forest trip',
            'date' => '1995-11-12',
            'approx_day' => 12,
            'approx_month' => 11,
            'approx_year' => 1995,
            'location' => 'Woodhill forest',
            'authorship' => 'John Smith',
            'description' => '<p>This trip was organised by Joe Blogs.</p><p>We had a very large turnout, with over 40 vehicles attending</p>',
            'video_url' => 'https://www.youtube.com/watch?v=3kjhd92387di',
            'video_type' => 'youtube',
            'video_id' => '3kjhd92387di',
            'cover_photo_id' => $coverPhoto->obfuscatedId()
        ]);
    }
}
