<?php

use App\Item;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RetrieveDraftTimelineTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_retrieve_items_in_cronological_order()
    {
        $user = factory(User::class)->create();
        $album = factory(Item::class)->states('album', 'draft')->create([
            'date' => Carbon::parse('October 7, 1982'),
            'approx_year' => 1982,
            'user_id' => $user->id,
        ]);
        $album2 = factory(Item::class)->states('album', 'draft')->create([
            'date' => null,
            'approx_year' => null,
            'user_id' => $user->id,
        ]);
        $milestone = factory(Item::class)->states('milestone', 'draft')->create([
            'date' => Carbon::parse('January 21, 1982'),
            'approx_year' => 1982,
            'user_id' => $user->id,
        ]);
        $milestone2 = factory(Item::class)->states('milestone', 'draft')->create([
            'date' => Carbon::parse('January 21, 1982'),
            'approx_year' => 1982,
            // for another user
        ]);
        $video = factory(Item::class)->states('video', 'draft')->create([
            'date' => Carbon::parse('April 5, 1982'),
            'approx_year' => 1982,
            'user_id' => $user->id,
        ]);
        $video2 = factory(Item::class)->states('video', 'draft')->create([
            'date' => Carbon::parse('November 12, 1969'),
            'approx_year' => 1969,
            'user_id' => $user->id,
        ]);
        $video3 = factory(Item::class)->states('video', 'draft')->create([
            'date' => Carbon::parse('November 12, 1969'),
            'approx_year' => 1969,
            // for another user
        ]);

        Auth::login($user);

        $this->json('GET', '/drafts/timeline');

        $this->seeStatusCode(200);
        $this->seeJsonStructure(['data', 'updated_at']);

        $this->assertCollectionEquals([$video2], $this->responseData('data.1969'));
        $this->assertCollectionEquals([$milestone, $video, $album], $this->responseData('data.1982'));
        $this->assertCollectionEquals([$album2], $this->responseData('data')[""]);
        $this->assertEquals($video2->updated_at, $this->responseData('updated_at'));
    }

    /** @test **/
    public function can_only_retrieve_published_items_not_drafts()
    {
        $user = factory(User::class)->create();
        $album = factory(Item::class)->states('album', 'published')->create([
            'date' => Carbon::parse('October 7, 1982'),
            'approx_year' => 1982,
            'user_id' => $user->id,
        ]);
        $milestone = factory(Item::class)->states('milestone', 'published')->create([
            'date' => Carbon::parse('January 21, 1982'),
            'approx_year' => 1982,
            'user_id' => $user->id,
        ]);
        $video = factory(Item::class)->states('video', 'draft')->create([
            'date' => Carbon::parse('April 5, 1982'),
            'approx_year' => 1982,
            'user_id' => $user->id,
        ]);

        Auth::login($user);

        $this->json('GET', '/timeline');

        $this->assertCollectionEquals([$milestone, $album], $this->responseData('data.1982'));
    }

    /** @test **/
    public function can_retrieve_all_drafts_as_an_editor()
    {
        $userA = factory(User::class)->create();
        $userB = factory(User::class)->create();
        $album = factory(Item::class)->states('album', 'draft')->create([
            'date' => Carbon::parse('October 7, 1982'),
            'approx_year' => 1982,
            'user_id' => $userA->id,
        ]);
        $album2 = factory(Item::class)->states('album', 'draft')->create([
            'date' => null,
            'approx_year' => null,
            'user_id' => $userB->id,
        ]);
        $milestone = factory(Item::class)->states('milestone', 'draft')->create([
            'date' => Carbon::parse('December 21, 1982'),
            'approx_year' => 1982,
            'user_id' => $userA->id,
        ]);
        $milestone2 = factory(Item::class)->states('milestone', 'draft')->create([
            'date' => Carbon::parse('March 21, 1982'),
            'approx_year' => 1982,
            'user_id' => $userB->id,
        ]);
        $video = factory(Item::class)->states('video', 'draft')->create([
            'date' => Carbon::parse('April 5, 1982'),
            'approx_year' => 1982,
            'user_id' => $userA->id,
        ]);
        $video2 = factory(Item::class)->states('video', 'draft')->create([
            'date' => Carbon::parse('November 12, 1969'),
            'approx_year' => 1969,
            'user_id' => $userB->id,
        ]);

        Auth::login(factory(User::class)->state('editor')->create());

        $this->json('GET', '/drafts/timeline?user=all');

        $this->seeStatusCode(200);
        $this->seeJsonStructure(['data', 'updated_at']);

        $this->assertCollectionEquals([$video2], $this->responseData('data.1969'));
        $this->assertCollectionEquals([$milestone2, $video, $album, $milestone], $this->responseData('data.1982'));
        $this->assertCollectionEquals([$album2], $this->responseData('data')[""]);
    }

    /** @test **/
    public function can_retrieve_drafts_belonging_to_a_specific_user_as_an_editor()
    {
        $userA = factory(User::class)->create();
        $userB = factory(User::class)->create();
        $album = factory(Item::class)->states('album', 'draft')->create([
            'date' => Carbon::parse('October 7, 1982'),
            'approx_year' => 1982,
            'user_id' => $userA->id,
        ]);
        $album2 = factory(Item::class)->states('album', 'draft')->create([
            'date' => null,
            'approx_year' => null,
            'user_id' => $userB->id,
        ]);
        $milestone = factory(Item::class)->states('milestone', 'draft')->create([
            'date' => Carbon::parse('December 21, 1982'),
            'approx_year' => 1982,
            'user_id' => $userA->id,
        ]);
        $milestone2 = factory(Item::class)->states('milestone', 'draft')->create([
            'date' => Carbon::parse('March 21, 1982'),
            'approx_year' => 1982,
            'user_id' => $userB->id,
        ]);
        $video = factory(Item::class)->states('video', 'draft')->create([
            'date' => Carbon::parse('April 5, 1982'),
            'approx_year' => 1982,
            'user_id' => $userA->id,
        ]);
        $video2 = factory(Item::class)->states('video', 'draft')->create([
            'date' => Carbon::parse('November 12, 1969'),
            'approx_year' => 1969,
            'user_id' => $userB->id,
        ]);

        Auth::login(factory(User::class)->state('editor')->create());

        $this->json('GET', '/drafts/timeline?user='.$userB->obfuscatedId());

        $this->seeStatusCode(200);
        $this->seeJsonStructure(['data', 'updated_at']);

        $this->assertCollectionEquals([$video2], $this->responseData('data.1969'));
        $this->assertCollectionEquals([$milestone2], $this->responseData('data.1982'));
        $this->assertCollectionEquals([$album2], $this->responseData('data')[""]);
    }
}
