<?php

use App\Item;
use Illuminate\Support\Carbon;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RetrieveTimelineTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_retrieve_items_in_cronological_order()
    {
        $album = factory(Item::class)->states('album', 'published')->create([
            'date' => Carbon::parse('October 7, 1982'),
            'approx_year' => 1982,
        ]);
        $album2 = factory(Item::class)->states('album', 'published')->create([
            'date' => null,
            'approx_year' => null,
        ]);
        $milestone = factory(Item::class)->states('milestone', 'published')->create([
            'date' => Carbon::parse('January 21, 1982'),
            'approx_year' => 1982,
        ]);
        $video = factory(Item::class)->states('video', 'published')->create([
            'date' => Carbon::parse('April 5, 1982'),
            'approx_year' => 1982,
        ]);
        $video2 = factory(Item::class)->states('video', 'published')->create([
            'date' => Carbon::parse('November 12, 1969'),
            'approx_year' => 1969,
        ]);

        $this->json('GET', '/timeline');

        $this->seeStatusCode(200);
        $this->seeJsonStructure(['data', 'updated_at']);

        $this->assertCollectionEquals([$video2], $this->responseData('data.1969'));
        $this->assertCollectionEquals([$milestone, $video, $album], $this->responseData('data.1982'));
        $this->assertCollectionEquals([$album2], $this->responseData('data')[""]);
        $this->assertEquals($video2->updated_at, $this->responseData('updated_at'));
    }

    /** @test **/
    public function can_retrieve_items_for_a_specific_year_in_cronological_order()
    {
        $album = factory(Item::class)->states('album', 'published')->create([
            'date' => Carbon::parse('October 7, 1982'),
            'approx_year' => 1982,
        ]);
        $milestone = factory(Item::class)->states('milestone', 'published')->create([
            'date' => Carbon::parse('January 21, 1982'),
            'approx_year' => 1982,
        ]);
        $video = factory(Item::class)->states('video', 'published')->create([
            'date' => Carbon::parse('April 5, 1982'),
            'approx_year' => 1982,
        ]);
        $video2 = factory(Item::class)->states('video', 'published')->create([
            'date' => Carbon::parse('November 12, 1969'),
            'approx_year' => 1969,
        ]);

        $this->json('GET', '/timeline/1982');

        $this->seeStatusCode(200);
        $this->seeJsonStructure(['data', 'updated_at']);

        $this->assertCollectionEquals([$milestone, $video, $album], $this->responseData('data'));
        $this->assertEquals($video2->updated_at, $this->responseData('updated_at'));
    }

    /** @test **/
    public function can_retrieve_items_without_a_year_in_cronological_order()
    {
        $album = factory(Item::class)->states('album', 'published')->create([
            'date' => null,
            'approx_year' => null,
        ]);
        $milestone = factory(Item::class)->states('milestone', 'published')->create([
            'date' => null,
            'approx_year' => null,
        ]);
        $video = factory(Item::class)->states('video', 'published')->create([
            'date' => null,
            'approx_year' => null,
        ]);

        $this->json('GET', '/timeline/none');

        $this->seeStatusCode(200);
        $this->seeJsonStructure(['data', 'updated_at']);

        $this->assertCollectionEquals([$album, $milestone, $video], $this->responseData('data'));
        $this->assertEquals($video->updated_at, $this->responseData('updated_at'));
    }

    /** @test **/
    public function can_only_retrieve_published_items_not_drafts()
    {
        $album = factory(Item::class)->states('album', 'published')->create([
            'date' => Carbon::parse('October 7, 1982'),
            'approx_year' => 1982,
        ]);
        $milestone = factory(Item::class)->states('milestone', 'published')->create([
            'date' => Carbon::parse('January 21, 1982'),
            'approx_year' => 1982,
        ]);
        $video = factory(Item::class)->states('video', 'draft')->create([
            'date' => Carbon::parse('April 5, 1982'),
            'approx_year' => 1982,
        ]);

        $this->json('GET', '/timeline/1982');

        $this->assertCollectionEquals([$milestone, $album], $this->responseData('data'));

        $this->json('GET', '/timeline');

        $this->assertCollectionEquals([$milestone, $album], $this->responseData('data.1982'));
    }
}
