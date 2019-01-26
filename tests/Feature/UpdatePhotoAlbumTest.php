<?php

use App\PhotoAlbum;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UpdatePhotoAlbumTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_update_an_unpublished_album()
    {
        $this->withoutExceptionHandling();

        $album = factory(PhotoAlbum::class)->states('unpublished')->create([
            'title' => 'Original title',
            'date' => '1990-01-01',
            'location' => 'Original location',
            'photographer' => 'Original photographer',
            'description' => '<p>Original description</p>',
        ]);

        $this->json('PATCH', '/photoalbums/'.$album->obfuscatedId(), [
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

        $this->json('PATCH', '/photoalbums/'.$album->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function can_update_title()
    {
        $album = factory(PhotoAlbum::class)->states('unpublished')->create([
            'title' => 'Original title'
        ]);

        $this->json('PATCH', '/photoalbums/'.$album->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('New title', $album->fresh()->title);
    }

    /** @test **/
    public function can_update_date()
    {
        $album = factory(PhotoAlbum::class)->states('unpublished')->create([
            'date' => '1990-01-01'
        ]);

        $this->json('PATCH', '/photoalbums/'.$album->obfuscatedId(), [
            'date' => '2018-12-31'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('2018-12-31', $album->fresh()->date->toDateString());
    }

    /** @test **/
    public function date_must_be_in_correct_format()
    {
        $album = factory(PhotoAlbum::class)->states('unpublished')->create([
            'date' => '1990-01-01'
        ]);

        $this->json('PATCH', '/photoalbums/'.$album->obfuscatedId(), [
            'date' => '2018/12/31'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('date');
    }

    /** @test **/
    public function can_update_location()
    {
        $album = factory(PhotoAlbum::class)->states('unpublished')->create([
            'location' => 'Original location'
        ]);

        $this->json('PATCH', '/photoalbums/'.$album->obfuscatedId(), [
            'location' => 'New location'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('New location', $album->fresh()->location);
    }

    /** @test **/
    public function can_update_photographer()
    {
        $album = factory(PhotoAlbum::class)->states('unpublished')->create([
            'photographer' => 'Original photographer'
        ]);

        $this->json('PATCH', '/photoalbums/'.$album->obfuscatedId(), [
            'photographer' => 'New photographer'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('New photographer', $album->fresh()->photographer);
    }

    /** @test **/
    public function can_update_description()
    {
        $album = factory(PhotoAlbum::class)->states('unpublished')->create([
            'description' => '<p>Original description</p>'
        ]);

        $this->json('PATCH', '/photoalbums/'.$album->obfuscatedId(), [
            'description' => '<p>New description</p>'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('<p>New description</p>', $album->fresh()->description);
    }

    /** @test **/
    public function cannot_update_without_a_valid_field()
    {
        $album = factory(PhotoAlbum::class)->states('unpublished')->create();

        $this->json('PATCH', '/photoalbums/'.$album->obfuscatedId(), []);

        $this->seeStatusCode(400);
    }
}
