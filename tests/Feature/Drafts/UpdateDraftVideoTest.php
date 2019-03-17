<?php

use App\Item;
use App\User;
use App\PhotoStore;
use App\Video\VideoInfo;
use App\Video\FakeVideoInfo;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UpdateDraftVideoTest extends TestCase
{
    use DatabaseMigrations, UpdateDraftItemContractTests;

    protected $itemState = 'video';

    protected $itemUrlPath = 'videos';

    public function setUp()
    {
        parent::setUp();

        $this->app->bind(VideoInfo::class, FakeVideoInfo::class);

        $this->app->instance(
            PhotoStore::class,
            Mockery::mock(PhotoStore::class)
                ->shouldReceive('putFileFromUrl')
                ->andReturn(true)
                ->getMock()
        );
    }

    /** @test **/
    public function can_update_a_draft_video()
    {
        $video = factory(Item::class)->states('video', 'draft')->create([
            'title' => 'Original title',
            'date' => '1990-01-01',
            'approx_day' => 1,
            'approx_month' => 1,
            'approx_year' => 1990,
            'location' => 'Original location',
            'authorship' => 'Original videographer',
            'description' => '<p>Original description</p>',
            'video_url' => 'https://www.youtube.com/watch?v=1234',
            'video_type' => 'youtube',
            'video_id' => '1234'
        ]);
        app('auth')->login($video->user);

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'title' => 'New title',
            'date' => '2018-12-31',
            'approx_day' => '31',
            'approx_month' => '12',
            'approx_year' => '2018',
            'location' => 'New location',
            'authorship' => 'New videographer',
            'description' => '<p>New description</p>',
            'video_url' => 'https://vimeo.com/304131475'
        ]);

        $this->seeStatusCode(200);

        $this->seeJsonStructure([
            'data' => [
                'id', 'title', 'date', 'approx_day', 'approx_month', 'approx_year',
                'location', 'authorship', 'description', 'video_url', 'video_type', 'video_id', 'cover_photo_id'
            ]
        ]);

        tap($video->fresh(), function ($video) {
            $this->seeJson([
                'id' => $video->obfuscatedId(),
                'title' => 'New title',
                'date' => '2018-12-31',
                'approx_day' => 31,
                'approx_month' => 12,
                'approx_year' => 2018,
                'location' => 'New location',
                'authorship' => 'New videographer',
                'description' => '<p>New description</p>',
                'video_url' => 'https://vimeo.com/304131475',
                'video_type' => 'vimeo',
                'video_id' => '304131475',
                'cover_photo_id' => $video->coverPhoto->obfuscatedId()
            ]);

            $this->assertEquals('New title', $video->title);
            $this->assertEquals('2018-12-31', $video->date->toDateString());
            $this->assertEquals(31, $video->approx_day);
            $this->assertEquals(12, $video->approx_month);
            $this->assertEquals(2018, $video->approx_year);
            $this->assertEquals('New location', $video->location);
            $this->assertEquals('New videographer', $video->authorship);
            $this->assertEquals('<p>New description</p>', $video->description);
            $this->assertEquals('https://vimeo.com/304131475', $video->video_url);
            $this->assertEquals('vimeo', $video->video_type);
            $this->assertEquals('304131475', $video->video_id);
        });
    }

    /** @test **/
    public function can_update_location()
    {
        $video = factory(Item::class)->states('video', 'draft')->create([
            'location' => 'Original location'
        ]);
        app('auth')->login($video->user);

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'location' => 'New location'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('New location', $video->fresh()->location);
    }

    /** @test **/
    public function can_update_authorship()
    {
        $video = factory(Item::class)->states('video', 'draft')->create([
            'authorship' => 'Original videographer'
        ]);
        app('auth')->login($video->user);

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'authorship' => 'New videographer'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('New videographer', $video->fresh()->authorship);
    }

    /** @test **/
    public function can_update_video_attributes()
    {
        $video = factory(Item::class)->states('video', 'draft')->create([
            'video_url' => 'https://www.youtube.com/watch?v=1234',
            'video_type' => 'youtube',
            'video_id' => '1234'
        ]);
        app('auth')->login($video->user);

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'video_url' => 'https://www.vimeo.com/123123123',
        ]);

        $this->seeStatusCode(200);

        tap($video->fresh(), function ($video) {
            $this->assertEquals('https://www.vimeo.com/123123123', $video->video_url);
            $this->assertEquals('vimeo', $video->video_type);
            $this->assertEquals('123123123', $video->video_id);
        });
    }

    /** @test **/
    public function video_attributes_only_updated_when_video_url_was_changed()
    {
        // mock FakeVideoInfo so test fails if any methods are called
        $this->app->bind(VideoInfo::class, Mockery::mock(FakeVideoInfo::class));

        $video = factory(Item::class)->states('video', 'draft')->create([
            'video_url' => 'https://www.youtube.com/watch?v=1234',
            'video_type' => 'youtube',
            'video_id' => '1234'
        ]);
        app('auth')->login($video->user);

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'video_url' => 'https://www.youtube.com/watch?v=1234',
        ]);

        $this->seeStatusCode(200);
    }
}
