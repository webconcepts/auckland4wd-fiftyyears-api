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
    use DatabaseMigrations;

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
    public function cannot_update_a_published_album()
    {
        $video = factory(Item::class)->states('video', 'published')->create([]);
        app('auth')->login($video->user);

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function can_update_title()
    {
        $video = factory(Item::class)->states('video', 'draft')->create([
            'title' => 'Original title'
        ]);
        app('auth')->login($video->user);

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('New title', $video->fresh()->title);
    }

    /** @test **/
    public function can_update_date()
    {
        $video = factory(Item::class)->states('video', 'draft')->create([
            'date' => '1990-01-01'
        ]);
        app('auth')->login($video->user);

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'date' => '2018-12-31'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('2018-12-31', $video->fresh()->date->toDateString());
    }

    /** @test **/
    public function date_must_be_in_correct_format()
    {
        $video = factory(Item::class)->states('video', 'draft')->create([
            'date' => '1990-01-01'
        ]);
        app('auth')->login($video->user);

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'date' => '2018/12/31'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('date');
    }

    /** @test **/
    public function can_update_approx_day()
    {
        $video = factory(Item::class)->states('video', 'draft')->create([
            'approx_day' => 2
        ]);
        app('auth')->login($video->user);

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'approx_day' => '15'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals(15, $video->fresh()->approx_day);
    }

    /** @test **/
    public function approx_day_must_be_between_1_and_31()
    {
        $video = factory(Item::class)->states('video', 'draft')->create();
        app('auth')->login($video->user);

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'approx_day' => '0'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_day');

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'approx_day' => '32'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_day');
    }

    /** @test **/
    public function can_update_approx_month()
    {
        $video = factory(Item::class)->states('video', 'draft')->create([
            'approx_month' => 4
        ]);
        app('auth')->login($video->user);

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'approx_month' => '11'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals(11, $video->fresh()->approx_month);
    }

    /** @test **/
    public function approx_month_must_be_between_1_and_12()
    {
        $video = factory(Item::class)->states('video', 'draft')->create();
        app('auth')->login($video->user);

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'approx_month' => '0'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_month');

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'approx_month' => '13'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_month');
    }

    /** @test **/
    public function can_update_approx_year()
    {
        $video = factory(Item::class)->states('video', 'draft')->create([
            'approx_year' => 1995
        ]);
        app('auth')->login($video->user);

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'approx_year' => '2001'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals(2001, $video->fresh()->approx_year);
    }

    /** @test **/
    public function approx_year_must_be_between_1969_and_2019()
    {
        $video = factory(Item::class)->states('video', 'draft')->create();
        app('auth')->login($video->user);

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'approx_year' => '1968'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_year');

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'approx_year' => '2020'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_year');
    }

    /** @test **/
    public function updating_approximate_date_values_updates_date_for_non_editors()
    {
        $video = factory(Item::class)->states('video', 'draft')->create([
            'date' => '1990-11-24',
            'approx_day' => null,
            'approx_month' => null,
            'approx_year' => null,
        ]);
        app('auth')->login($video->user);

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'approx_year' => '2001'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('2001-01-01', $video->fresh()->date->toDateString());

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'approx_month' => '5'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('2001-05-01', $video->fresh()->date->toDateString());

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'approx_day' => '13'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('2001-05-13', $video->fresh()->date->toDateString());
    }

    /** @test **/
    public function updating_approximate_date_will_not_update_date_for_an_editor()
    {
        $user = factory(User::class)->states('editor')->create();
        $video = factory(Item::class)->states('video', 'draft')->create([
            'user_id' => $user->id,
            'date' => '1990-11-24',
            'approx_day' => 24,
            'approx_month' => 11,
            'approx_year' => 1990,
        ]);
        app('auth')->login($user);

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'approx_year' => '2001',
            'approx_month' => '12',
            'approx_day' => '2'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('1990-11-24', $video->fresh()->date->toDateString());
        $this->assertEquals(2, $video->fresh()->approx_day);
        $this->assertEquals(12, $video->fresh()->approx_month);
        $this->assertEquals(2001, $video->fresh()->approx_year);
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
    public function can_update_description()
    {
        $video = factory(Item::class)->states('video', 'draft')->create([
            'description' => '<p>Original description</p>'
        ]);
        app('auth')->login($video->user);

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'description' => '<p>New description</p>'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('<p>New description</p>', $video->fresh()->description);
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

    /** @test **/
    public function cannot_update_without_a_valid_field()
    {
        $video = factory(Item::class)->states('video', 'draft')->create();
        app('auth')->login($video->user);

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), []);

        $this->seeStatusCode(400);
    }

    /** @test **/
    public function cannot_update_when_not_logged_in()
    {
        $video = factory(Item::class)->states('video', 'draft')->create();

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(401);
    }

    /** @test **/
    public function cannot_update_someone_elses_album()
    {
        $video = factory(Item::class)->states('video', 'draft')->create();
        app('auth')->login(factory(User::class)->create());

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(403);
    }

    /** @test **/
    public function can_update_someone_elses_album_as_an_editor()
    {
        $video = factory(Item::class)->states('video', 'draft')->create();
        app('auth')->login(factory(User::class)->states('editor')->create());

        $this->json('PATCH', '/drafts/videos/'.$video->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(200);
    }
}
