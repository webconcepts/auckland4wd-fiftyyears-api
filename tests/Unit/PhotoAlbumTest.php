<?php

use App\Photo;
use Carbon\Carbon;
use App\PhotoAlbum;
use App\IdObfuscator;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class PhotoAlbumTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function photo_albums_with_a_published_at_date_and_without_a_removed_at_date_are_published()
    {
        $publishedAlbumA = factory(PhotoAlbum::class)->create(['published_at' => Carbon::parse('-1 week')]);
        $draftAlbum = factory(PhotoAlbum::class)->create(['published_at' => null]);
        $removedAlbum = factory(PhotoAlbum::class)->create(['removed_at' => Carbon::parse('-1 week')]);
        $publishedAlbumB = factory(PhotoAlbum::class)->create(['published_at' => Carbon::parse('-2 days')]);

        $publishedAlbums = PhotoAlbum::published()->get();

        $this->assertTrue($publishedAlbums->contains($publishedAlbumA));
        $this->assertTrue($publishedAlbums->contains($publishedAlbumB));
        $this->assertFalse($publishedAlbums->contains($draftAlbum));
        $this->assertFalse($publishedAlbums->contains($removedAlbum));
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
    public function removed_photo_albums_cannot_be_published()
    {
        $album = factory(PhotoAlbum::class)->states('removed')->create();
        $this->assertFalse($album->isPublished());

        $album->publish();

        $this->assertFalse($album->isPublished());
    }

    /** @test */
    public function photo_albums_without_a_published_at_date_and_removed_at_date_are_draft()
    {
        $draftAlbumA = factory(PhotoAlbum::class)->create(['published_at' => null]);
        $publishedAlbum = factory(PhotoAlbum::class)->create(['published_at' => Carbon::parse('-2 days')]);
        $removedAlbum = factory(PhotoAlbum::class)->create(['removed_at' => Carbon::parse('-1 week')]);
        $draftAlbumB = factory(PhotoAlbum::class)->create(['published_at' => null]);

        $draftAlbums = PhotoAlbum::draft()->get();

        $this->assertTrue($draftAlbums->contains($draftAlbumA));
        $this->assertTrue($draftAlbums->contains($draftAlbumB));
        $this->assertFalse($draftAlbums->contains($publishedAlbum));
        $this->assertFalse($draftAlbums->contains($removedAlbum));
    }

    /** @test **/
    public function photo_albums_can_be_unpublished_to_return_to_a_draft()
    {
        $album = factory(PhotoAlbum::class)->create(['published_at' => Carbon::parse('-2 days')]);
        $this->assertFalse($album->isDraft());

        $album->unpublish();

        $this->assertTrue($album->isDraft());
    }

    /** @test **/
    public function draft_photo_albums_can_be_removed()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create();
        $this->assertFalse($album->isRemoved());

        $album->remove();

        $this->assertTrue($album->isRemoved());
    }

    /** @test **/
    public function published_photo_albums_cannot_be_removed()
    {
        $album = factory(PhotoAlbum::class)->states('published')->create();
        $this->assertFalse($album->isRemoved());

        $album->remove();

        $this->assertFalse($album->isRemoved());
    }

    /** @test **/
    public function converting_to_an_array()
    {
        $album = factory(PhotoAlbum::class)->create([
            'title' => 'Woodhill forest trip',
            'date' => Carbon::parse('November 12, 1995'),
            'approx_day' => 12,
            'approx_month' => 11,
            'approx_year' => 1995,
            'location' => 'Woodhill forest',
            'photographer' => 'John Smith',
            'description' => '<p>This trip was organised by Joe Blogs.</p><p>We had a very large turnout, with over 40 vehicles attending</p>',
        ]);

        $idObfuscator = Mockery::mock(IdObfuscator::class);
        $idObfuscator->shouldReceive('encode')->with(1)->andReturn('OBFUSCATEDID1');

        $this->app->instance(IdObfuscator::class, $idObfuscator);

        $result = $album->toArray();

        $this->assertEquals([
            'id' => 'OBFUSCATEDID1',
            'title' => 'Woodhill forest trip',
            'date' => '1995-11-12',
            'approx_day' => 12,
            'approx_month' => 11,
            'approx_year' => 1995,
            'location' => 'Woodhill forest',
            'photographer' => 'John Smith',
            'description' => '<p>This trip was organised by Joe Blogs.</p><p>We had a very large turnout, with over 40 vehicles attending</p>',
        ], $result);
    }

    /** @test **/
    public function approximate_date_values_should_return_null_not_0_when_converting_to_array()
    {
        $album = factory(PhotoAlbum::class)->create([
            'approx_day' => null,
            'approx_month' => null,
            'approx_year' => null,
        ]);

        $result = $album->toArray();

        $this->assertEquals(null, $result['approx_day']);
        $this->assertEquals(null, $result['approx_month']);
        $this->assertEquals(null, $result['approx_year']);
    }

    /** @test **/
    public function get_next_available_number_for_photos_in_this_album()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create();
        factory(Photo::class)->create(['number' => 1, 'photo_album_id' => $album->id]);
        factory(Photo::class)->create(['number' => 2, 'photo_album_id' => $album->id]);
        factory(Photo::class)->create(['number' => 4, 'photo_album_id' => $album->id]);

        $number = $album->getNextAvailablePhotoNumber();

        $this->assertEquals(5, $number);
    }

    /** @test **/
    public function get_next_available_number_for_photos_when_no_photos_exist()
    {
        $album = factory(PhotoAlbum::class)->states('draft')->create();

        $number = $album->getNextAvailablePhotoNumber();

        $this->assertEquals(1, $number);
    }
}
