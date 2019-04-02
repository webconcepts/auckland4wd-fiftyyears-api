<?php

use App\Item;
use App\Photo;
use Illuminate\Support\Carbon;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RetrieveSlideshowTest extends TestCase
{
    use DatabaseMigrations;

    protected function photo($album, $data = [])
    {
        return factory(Photo::class)->state('uploaded')->create(array_merge([
            'item_id' => $album->id
        ], $data));
    }

    /** @test **/
    public function get_random_images_for_a_random_album()
    {
        $this->withoutExceptionHandling();

        $album = factory(Item::class)->states('album', 'published')->create();
        $photoA = $this->photo($album, ['likes' => 0]);
        $photoB = $this->photo($album, ['likes' => 0]);
        $photoC = $this->photo($album, ['likes' => 5]);
        $photoD = $this->photo($album, ['likes' => 10]);
        $photoE = $this->photo($album, ['likes' => 0]);
        $photoF = $this->photo($album, ['likes' => 1]);

        $this->json('GET', '/slideshow', ['number' => 4]);

        $this->seeStatusCode(200);

        $this->seeJsonStructure([
            'data' => ['id', 'title', 'approx_month', 'approx_year', 'location', 'authorship'],
            'photos',
            'offset'
        ]);

        $photos = $this->responseData('photos');

        $this->assertEquals(4, $photos->count());
        $this->assertTrue($photos->contains('id', $photoC->id));
        $this->assertTrue($photos->contains('id', $photoD->id));
        $this->assertTrue($photos->contains('id', $photoF->id));
    }

    /** @test **/
    public function photos_with_more_likes_take_presidence()
    {
        $album = factory(Item::class)->states('album', 'published')->create();
        $photoA = $this->photo($album, ['likes' => 1]);
        $photoB = $this->photo($album, ['likes' => 10]);
        $photoC = $this->photo($album, ['likes' => 3]);
        $photoD = $this->photo($album, ['likes' => 8]);
        $photoE = $this->photo($album, ['likes' => 12]);
        $photoF = $this->photo($album, ['likes' => 6]);

        $this->json('GET', '/slideshow', ['number' => 3]);

        $this->seeStatusCode(200);

        $photos = $this->responseData('photos');
        $this->assertTrue($photos->contains('id', $photoB->id));
        $this->assertTrue($photos->contains('id', $photoD->id));
        $this->assertTrue($photos->contains('id', $photoE->id));
    }

    /** @test **/
    public function photos_ordered_by_number()
    {
        $album = factory(Item::class)->states('album', 'published')->create();
        $photoA = $this->photo($album, ['number' => 2]);
        $photoB = $this->photo($album, ['number' => 4]);
        $photoC = $this->photo($album, ['number' => 1]);
        $photoD = $this->photo($album, ['number' => 5]);
        $photoE = $this->photo($album, ['number' => 3]);

        $this->json('GET', '/slideshow', ['number' => 5]);

        $this->assertCollectionEquals([$photoC, $photoA, $photoE, $photoB, $photoD], $this->responseData('photos'));
    }

    /** @test **/
    public function albums_can_be_retreived_in_order_using_offset()
    {
        $albumA = factory(Item::class)->states('album', 'published')->create(['date' => Carbon::parse('1995-12-22')]);
        $this->photo($albumA);
        $this->photo($albumA);
        $albumB = factory(Item::class)->states('album', 'published')->create(['date' => Carbon::parse('1969-01-12')]);
        $this->photo($albumB);
        $this->photo($albumB);
        $albumC = factory(Item::class)->states('album', 'published')->create(['date' => Carbon::parse('2002-05-01')]);
        $this->photo($albumC);
        $this->photo($albumC);

        $this->json('GET', '/slideshow', ['number' => 2, 'offset' => 1]);
        $this->assertEquals($albumB->id, $this->responseData('data')->id);

        $this->json('GET', '/slideshow', ['number' => 2, 'offset' => 2]);
        $this->assertEquals($albumA->id, $this->responseData('data')->id);

        $this->json('GET', '/slideshow', ['number' => 2, 'offset' => 3]);
        $this->assertEquals($albumC->id, $this->responseData('data')->id);
    }

    /** @test **/
    public function will_return_less_photos_then_number_when_less_exist()
    {
        $album = factory(Item::class)->states('album', 'published')->create();
        $photoA = $this->photo($album, ['likes' => 1]);
        $photoB = $this->photo($album, ['likes' => 10]);
        $photoC = $this->photo($album, ['likes' => 3]);

        $this->json('GET', '/slideshow', ['number' => 6]);

        $this->seeStatusCode(200);

        $this->assertEquals(3, $this->responseData('photos')->count());
    }

    /** @test **/
    public function number_must_be_a_positive_integer()
    {
        $this->json('GET', '/slideshow', ['number' => -2]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('number');

        $this->json('GET', '/slideshow', ['number' => 1.5]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('number');
    }

    /** @test **/
    public function offset_must_be_a_positive_integer()
    {
        $this->json('GET', '/slideshow', ['offset' => -2]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('offset');

        $this->json('GET', '/slideshow', ['offset' => 1.5]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('offset');
    }
}
