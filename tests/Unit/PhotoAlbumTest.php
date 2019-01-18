<?php

use Carbon\Carbon;
use App\PhotoAlbum;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class PhotoAlbumTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function photo_albums_with_a_published_at_date_are_published()
    {
        $publishedAlbumA = factory(PhotoAlbum::class)->create(['published_at' => Carbon::parse('-1 week')]);
        $publishedAlbumB = factory(PhotoAlbum::class)->create(['published_at' => Carbon::parse('-2 days')]);
        $unpublishedAlbum = factory(PhotoAlbum::class)->create(['published_at' => null]);

        $publishedAlbums = PhotoAlbum::published()->get();

        $this->assertTrue($publishedAlbums->contains($publishedAlbumA));
        $this->assertTrue($publishedAlbums->contains($publishedAlbumB));
        $this->assertFalse($publishedAlbums->contains($unpublishedAlbum));
    }

    /** @test **/
    public function photo_albums_can_be_published()
    {
        $album = factory(PhotoAlbum::class)->create(['published_at' => null]);
        $this->assertFalse($album->isPublished());

        $album->publish();

        $this->assertTrue($album->isPublished());
    }

    /** @test **/
    public function converting_to_an_array()
    {
        $album = factory(PhotoAlbum::class)->create([
            'title' => 'Woodhill forest trip',
            'date' => Carbon::parse('November 12, 1995'),
            'location' => 'Woodhill forest',
            'photographer' => 'John Smith',
            'description' => '<p>This trip was organised by Joe Blogs.</p><p>We had a very large turnout, with over 40 vehicles attending</p>',
        ]);

        $result = $album->toArray();

        $this->assertEquals([
            'title' => 'Woodhill forest trip',
            'date' => '1995-11-12',
            'location' => 'Woodhill forest',
            'photographer' => 'John Smith',
            'description' => '<p>This trip was organised by Joe Blogs.</p><p>We had a very large turnout, with over 40 vehicles attending</p>',
        ], $result);
    }
}