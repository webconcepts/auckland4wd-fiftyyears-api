<?php

use App\Item;
use App\User;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UpdateDraftPhotoAlbumTest extends TestCase
{
    use DatabaseMigrations, UpdateDraftItemContractTests;

    protected $itemState = 'album';

    protected $itemUrlPath = 'photo-albums';

    /** @test **/
    public function can_update_a_draft_album()
    {
        $album = factory(Item::class)->states('album', 'draft')->create([
            'title' => 'Original title',
            'date' => '1990-01-01',
            'approx_day' => 1,
            'approx_month' => 1,
            'approx_year' => 1990,
            'location' => 'Original location',
            'authorship' => 'Original photographer',
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
            'authorship' => 'New photographer',
            'description' => '<p>New description</p>',
        ]);

        $this->seeStatusCode(200);

        $this->seeJsonStructure([
            'data' => [
                'id', 'title', 'date', 'approx_day', 'approx_month', 'approx_year', 'location', 'authorship', 'description'
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
            'authorship' => 'New photographer',
            'description' => '<p>New description</p>',
        ]);

        tap($album->fresh(), function ($album) {
            $this->assertEquals('New title', $album->title);
            $this->assertEquals('2018-12-31', $album->date->toDateString());
            $this->assertEquals(31, $album->approx_day);
            $this->assertEquals(12, $album->approx_month);
            $this->assertEquals(2018, $album->approx_year);
            $this->assertEquals('New location', $album->location);
            $this->assertEquals('New photographer', $album->authorship);
            $this->assertEquals('<p>New description</p>', $album->description);
        });
    }

    /** @test **/
    public function can_update_location()
    {
        $album = factory(Item::class)->states('album', 'draft')->create([
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
    public function can_update_authorship()
    {
        $album = factory(Item::class)->states('album', 'draft')->create([
            'authorship' => 'Original photographer'
        ]);
        app('auth')->login($album->user);

        $this->json('PATCH', '/drafts/photo-albums/'.$album->obfuscatedId(), [
            'authorship' => 'New photographer'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('New photographer', $album->fresh()->authorship);
    }
}
