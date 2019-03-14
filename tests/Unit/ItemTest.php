<?php

use App\Item;
use App\Photo;
use Carbon\Carbon;
use App\PhotoStore;
use App\IdObfuscator;
use App\Video\VideoInfo;
use App\Video\FakeVideoInfo;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ItemTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->app->bind(VideoInfo::class, FakeVideoInfo::class);

        $this->app->instance(
            PhotoStore::class,
            Mockery::mock(PhotoStore::class)
                ->shouldReceive('putFileFromURL')
                ->andReturn(true)
                ->getMock()
        );
    }

    /** @test */
    public function items_with_a_published_at_date_and_without_a_removed_at_date_are_published()
    {
        $publishedItemA = factory(Item::class)->create(['published_at' => Carbon::parse('-1 week')]);
        $draftItem = factory(Item::class)->create(['published_at' => null]);
        $removedItem = factory(Item::class)->create(['removed_at' => Carbon::parse('-1 week')]);
        $publishedItemB = factory(Item::class)->create(['published_at' => Carbon::parse('-2 days')]);

        $publishedItems = Item::published()->get();

        $this->assertTrue($publishedItems->contains($publishedItemA));
        $this->assertTrue($publishedItems->contains($publishedItemB));
        $this->assertFalse($publishedItems->contains($draftItem));
        $this->assertFalse($publishedItems->contains($removedItem));
    }

    /** @test **/
    public function items_can_be_published()
    {
        $item = factory(Item::class)->create(['published_at' => null]);
        $this->assertFalse($item->isPublished());

        $item->publish();

        $this->assertTrue($item->isPublished());
    }

    /** @test **/
    public function removed_items_cannot_be_published()
    {
        $item = factory(Item::class)->states('removed')->create();
        $this->assertFalse($item->isPublished());

        $item->publish();

        $this->assertFalse($item->isPublished());
    }

    /** @test */
    public function items_without_a_published_at_date_and_removed_at_date_are_draft()
    {
        $draftItemA = factory(Item::class)->create(['published_at' => null]);
        $publishedItem = factory(Item::class)->create(['published_at' => Carbon::parse('-2 days')]);
        $removedItem = factory(Item::class)->create(['removed_at' => Carbon::parse('-1 week')]);
        $draftItemB = factory(Item::class)->create(['published_at' => null]);

        $draftItems = Item::draft()->get();

        $this->assertTrue($draftItems->contains($draftItemA));
        $this->assertTrue($draftItems->contains($draftItemB));
        $this->assertFalse($draftItems->contains($publishedItem));
        $this->assertFalse($draftItems->contains($removedItem));
    }

    /** @test **/
    public function items_can_be_unpublished_to_return_to_a_draft()
    {
        $item = factory(Item::class)->create(['published_at' => Carbon::parse('-2 days')]);
        $this->assertFalse($item->isDraft());

        $item->unpublish();

        $this->assertTrue($item->isDraft());
    }

    /** @test **/
    public function draft_items_can_be_removed()
    {
        $item = factory(Item::class)->states('draft')->create();
        $this->assertFalse($item->isRemoved());

        $item->remove();

        $this->assertTrue($item->isRemoved());
    }

    /** @test **/
    public function published_items_cannot_be_removed()
    {
        $item = factory(Item::class)->states('published')->create();
        $this->assertFalse($item->isRemoved());

        $item->remove();

        $this->assertFalse($item->isRemoved());
    }

    /** @test **/
    public function converting_to_an_array()
    {
        $item = factory(Item::class)->create([
            'title' => 'Woodhill forest trip',
            'date' => Carbon::parse('November 12, 1995'),
            'approx_day' => 12,
            'approx_month' => 11,
            'approx_year' => 1995,
            'location' => 'Woodhill forest',
            'authorship' => 'John Smith',
            'description' => '<p>This trip was organised by Joe Blogs.</p><p>We had a very large turnout, with over 40 vehicles attending</p>',
            'cover_photo_id' => null
        ]);

        $idObfuscator = Mockery::mock(IdObfuscator::class);
        $idObfuscator->shouldReceive('encode')->with(1)->andReturn('OBFUSCATEDID1');

        $this->app->instance(IdObfuscator::class, $idObfuscator);

        $result = $item->toArray();

        $this->assertEquals([
            'id' => 'OBFUSCATEDID1',
            'title' => 'Woodhill forest trip',
            'date' => '1995-11-12',
            'approx_day' => 12,
            'approx_month' => 11,
            'approx_year' => 1995,
            'location' => 'Woodhill forest',
            'authorship' => 'John Smith',
            'description' => '<p>This trip was organised by Joe Blogs.</p><p>We had a very large turnout, with over 40 vehicles attending</p>',
            'cover_photo_id' => null
        ], $result);
    }

    /** @test **/
    public function approximate_date_values_should_return_null_not_0_when_converting_to_array()
    {
        $item = factory(Item::class)->create([
            'approx_day' => null,
            'approx_month' => null,
            'approx_year' => null,
        ]);

        $result = $item->toArray();

        $this->assertEquals(null, $result['approx_day']);
        $this->assertEquals(null, $result['approx_month']);
        $this->assertEquals(null, $result['approx_year']);
    }

    /** @test **/
    public function html_stripped_when_setting_title_value()
    {
        $item = factory(Item::class)->make();

        $item->title = '<h1>This is an <strong>awesome</strong></ br> title</h1>';

        $this->assertEquals('This is an awesome title', $item->title);
    }

    /** @test **/
    public function html_stripped_when_setting_location_value()
    {
        $item = factory(Item::class)->make();

        $item->location = '<script></script>New location';

        $this->assertEquals('New location', $item->location);
    }

    /** @test **/
    public function html_stripped_when_setting_authorship_value()
    {
        $item = factory(Item::class)->make();

        $item->authorship = 'Authors <img src="something<here">name';

        $this->assertEquals('Authors name', $item->authorship);
    }

    /** @test **/
    public function html_stripped_when_setting_description_value_except_p_and_br_tags()
    {
        $item = factory(Item::class)->make();

        $item->description = '<p>This is a <strong>description</string></p><p>It has <u>paragraphs</u><br />and <em>line breaks</em></p>';

        $this->assertEquals(
            '<p>This is a description</p><p>It has paragraphs<br />and line breaks</p>',
            $item->description
        );
    }

    /** @test **/
    public function can_set_video_type_to_only_youtube_or_vimeo()
    {
        $item = factory(Item::class)->make();
        $this->assertNull($item->video_type);

        $item->video_type = 'youtube';
        $this->assertEquals('youtube', $item->video_type);

        $item->video_type = 'vimeo';
        $this->assertEquals('vimeo', $item->video_type);

        $item->video_type = 'facebook';
        $this->assertNull($item->video_type);
    }

    /** @test **/
    public function can_set_video_type_and_id_from_video_url()
    {
        $item = factory(Item::class)->create();
        $this->assertNull($item->video_url);
        $this->assertNull($item->video_type);
        $this->assertNull($item->video_id);

        Auth::login($item->user);

        $item->video_url = 'https://www.youtube.com/watch?v=C4kxS1ksqtw';
        $item->setVideoDetailsFromUrl();

        $this->assertEquals('youtube', $item->video_type);
        $this->assertEquals('C4kxS1ksqtw', $item->video_id);
    }

    /** @test **/
    public function video_cover_photo_created_and_uploaded_when_setting_video_details()
    {
        $item = factory(Item::class)->create();

        Auth::login($item->user);

        $item->video_url = 'https://www.youtube.com/watch?v=C4kxS1ksqtw';
        $item->setVideoDetailsFromUrl();

        $photo = $item->coverPhoto;
        $this->assertTrue($photo->isUploaded());
        $this->assertEquals($item->id, $photo->item_id);
        $this->assertEquals($photo->id, $item->fresh()->cover_photo_id);
    }
}
