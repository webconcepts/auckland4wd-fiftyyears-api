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
            'location' => 'Original location',
            'photographer' => 'Original photographer',
            'description' => '<p>Original description</p>'
        ]);
        app('auth')->login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'title' => 'New title',
            'date' => '2018-12-31',
            'location' => 'New location',
            'photographer' => 'New photographer',
            'description' => '<p>New description</p>',
        ]);

        $this->seeStatusCode(200);

        $this->seeJsonStructure([
            'data' => [
                'id', 'title', 'date', 'location', 'photographer', 'description'
            ]
        ]);
        $this->seeJson([
            'id' => $album->obfuscatedId(),
            'title' => 'New title',
            'date' => '2018-12-31',
            'location' => 'New location',
            'photographer' => 'New photographer',
            'description' => '<p>New description</p>',
        ]);

        tap($album->fresh(), function ($album) {
            $this->assertEquals('New title', $album->title);
            $this->assertEquals('2018-12-31', $album->date->toDateString());
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
