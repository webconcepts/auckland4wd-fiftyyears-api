<?php

use App\Item;
use Carbon\Carbon;
use App\IdObfuscator;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RetrieveVideoTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_retrieve_a_published_photo_album()
    {
        $video = factory(Item::class)->states('video', 'published')->create([
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
        ]);

        $this->json('GET', '/videos/'.$video->obfuscatedId());

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                'id', 'title', 'date', 'approx_day', 'approx_month', 'approx_year', 'location',
                'authorship', 'description', 'video_url', 'video_type', 'video_id'
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
            'video_id' => '3kjhd92387di'
        ]);
    }

    /** @test **/
    public function cannot_retrieve_a_draft_photo_album()
    {
        $video = factory(Item::class)->states('video', 'draft')->create();

        $this->json('GET', '/videos/'.$video->obfuscatedId());

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function can_retrieve_a_list_of_only_published_photo_albums()
    {
        $this->withoutExceptionHandling();

        $videoA = factory(Item::class)->states('video', 'published')->create();
        $draft = factory(Item::class)->states('video', 'draft')->create();
        $videoB = factory(Item::class)->states('video', 'published')->create();
        $videoC = factory(Item::class)->states('video', 'published')->create();

        $this->json('GET', '/videos');
        $content = json_decode($this->response->getContent());

        $this->seeStatusCode(200);
        $this->seeJson();

        $this->assertCollectionEquals([
            $videoA,
            $videoB,
            $videoC
        ], $this->responseData('data'));
    }
}
