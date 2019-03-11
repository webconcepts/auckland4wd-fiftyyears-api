<?php

use App\Item;
use App\Photo;
use Carbon\Carbon;
use App\IdObfuscator;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ItemTest extends TestCase
{
    use DatabaseMigrations;

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
}
