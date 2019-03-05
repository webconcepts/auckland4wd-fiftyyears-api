<?php

use App\User;
use App\PhotoAlbum;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UpdateDraftPhotoAlbumTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_update_a_draft_album()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create([
            'title' => 'Original title',
            'date' => '1990-01-01',
            'approx_day' => 1,
            'approx_month' => 1,
            'approx_year' => 1990,
            'location' => 'Original location',
            'photographer' => 'Original photographer',
            'description' => '<p>Original description</p>'
        ]);
        app('auth')->login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'title' => 'New title',
            'date' => '2018-12-31',
            'approx_day' => '31',
            'approx_month' => '12',
            'approx_year' => '2018',
            'location' => 'New location',
            'photographer' => 'New photographer',
            'description' => '<p>New description</p>',
        ]);

        $this->seeStatusCode(200);

        $this->seeJsonStructure([
            'data' => [
                'id', 'title', 'date', 'approx_day', 'approx_month', 'approx_year', 'location', 'photographer', 'description'
            ]
        ]);
        $this->seeJson([
            'id' => $album->obfuscatedId(),
            'title' => 'New title',
            'date' => '2018-12-31',
            'approx_day' => 31,
            'approx_month' => 12,
            'approx_year' => 2018,
            'location' => 'New location',
            'photographer' => 'New photographer',
            'description' => '<p>New description</p>',
        ]);

        tap($album->fresh(), function ($album) {
            $this->assertEquals('New title', $album->title);
            $this->assertEquals('2018-12-31', $album->date->toDateString());
            $this->assertEquals(31, $album->approx_day);
            $this->assertEquals(12, $album->approx_month);
            $this->assertEquals(2018, $album->approx_year);
            $this->assertEquals('New location', $album->location);
            $this->assertEquals('New photographer', $album->photographer);
            $this->assertEquals('<p>New description</p>', $album->description);
        });
    }

    /** @test **/
    public function cannot_update_a_published_album()
    {
        $album = factory(PhotoAlbum::class)->states('published')->create([]);
        app('auth')->login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function can_update_title()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create([
            'title' => 'Original title'
        ]);
        app('auth')->login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('New title', $album->fresh()->title);
    }

    /** @test **/
    public function can_update_date()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create([
            'date' => '1990-01-01'
        ]);
        app('auth')->login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'date' => '2018-12-31'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('2018-12-31', $album->fresh()->date->toDateString());
    }

    /** @test **/
    public function date_must_be_in_correct_format()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create([
            'date' => '1990-01-01'
        ]);
        app('auth')->login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'date' => '2018/12/31'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('date');
    }

    /** @test **/
    public function can_update_approx_day()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create([
            'approx_day' => 2
        ]);
        app('auth')->login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'approx_day' => '15'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals(15, $album->fresh()->approx_day);
    }

    /** @test **/
    public function approx_day_must_be_between_1_and_31()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create();
        app('auth')->login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'approx_day' => '0'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_day');

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'approx_day' => '32'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_day');
    }

    /** @test **/
    public function can_update_approx_month()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create([
            'approx_month' => 4
        ]);
        app('auth')->login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'approx_month' => '11'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals(11, $album->fresh()->approx_month);
    }

    /** @test **/
    public function approx_month_must_be_between_1_and_12()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create();
        app('auth')->login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'approx_month' => '0'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_month');

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'approx_month' => '13'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_month');
    }

    /** @test **/
    public function can_update_approx_year()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create([
            'approx_year' => 1995
        ]);
        app('auth')->login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'approx_year' => '2001'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals(2001, $album->fresh()->approx_year);
    }

    /** @test **/
    public function approx_year_must_be_between_1969_and_2019()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create();
        app('auth')->login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'approx_year' => '1968'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_year');

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'approx_year' => '2020'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_year');
    }

    /** @test **/
    public function updating_approximate_date_values_updates_date_for_non_editors()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create([
            'date' => '1990-11-24',
            'approx_day' => null,
            'approx_month' => null,
            'approx_year' => null,
        ]);
        app('auth')->login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'approx_year' => '2001'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('2001-01-01', $album->fresh()->date->toDateString());

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'approx_month' => '5'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('2001-05-01', $album->fresh()->date->toDateString());

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'approx_day' => '13'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('2001-05-13', $album->fresh()->date->toDateString());
    }

    /** @test **/
    public function updating_approximate_date_will_not_update_date_for_an_editor()
    {
        $user = factory(User::class)->states('editor')->create();
        $album = factory(PhotoAlbum::class)->states('draft')->create([
            'user_id' => $user->id,
            'date' => '1990-11-24',
            'approx_day' => 24,
            'approx_month' => 11,
            'approx_year' => 1990,
        ]);
        app('auth')->login($user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'approx_year' => '2001',
            'approx_month' => '12',
            'approx_day' => '2'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('1990-11-24', $album->fresh()->date->toDateString());
        $this->assertEquals(2, $album->fresh()->approx_day);
        $this->assertEquals(12, $album->fresh()->approx_month);
        $this->assertEquals(2001, $album->fresh()->approx_year);
    }

    /** @test **/
    public function can_update_location()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create([
            'location' => 'Original location'
        ]);
        app('auth')->login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'location' => 'New location'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('New location', $album->fresh()->location);
    }

    /** @test **/
    public function can_update_photographer()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create([
            'photographer' => 'Original photographer'
        ]);
        app('auth')->login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'photographer' => 'New photographer'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('New photographer', $album->fresh()->photographer);
    }

    /** @test **/
    public function can_update_description()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create([
            'description' => '<p>Original description</p>'
        ]);
        app('auth')->login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'description' => '<p>New description</p>'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('<p>New description</p>', $album->fresh()->description);
    }

    /** @test **/
    public function cannot_update_without_a_valid_field()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create();
        app('auth')->login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), []);

        $this->seeStatusCode(400);
    }

    /** @test **/
    public function cannot_update_when_not_logged_in()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create();

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(401);
    }

    /** @test **/
    public function cannot_update_someone_elses_album()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create();
        app('auth')->login(factory(User::class)->create());

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(403);
    }

    /** @test **/
    public function can_update_someone_elses_album_as_an_editor()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create();
        app('auth')->login(factory(User::class)->states('editor')->create());

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(200);
    }
}
