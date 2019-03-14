<?php

use App\Item;
use App\User;
use App\Photo;
use Carbon\Carbon;
use App\IdObfuscator;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RetrieveDraftVideoTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_retrieve_a_draft_video()
    {
        $coverPhoto = factory(Photo::class)->states('uploaded')->create();
        $video = factory(Item::class)->states('video', 'draft')->create([
            'title' => 'Woodhill forest trip',
            'date' => Carbon::parse('November 12, 1995'),
            'approx_day' => 12,
            'approx_month' => 11,
            'approx_year' => 1995,
            'location' => 'Woodhill forest',
            'authorship' => 'John Smith',
            'description' => '<p>This trip was organised by Joe Blogs.</p><p>We had a very large turnout, with over 40 vehicles attending</p>',
            'video_url' => 'https://www.youtube.com/watch?v=3kjhd92387di',
            'video_type' => 'youtube',
            'video_id' => '3kjhd92387di',
            'cover_photo_id' => $coverPhoto->id,
        ]);

        app('auth')->login($video->user);

        $this->json('GET', '/drafts/videos/'.$video->obfuscatedId());

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                'id', 'title', 'date', 'approx_day', 'approx_month', 'approx_year',
                'location', 'authorship', 'description', 'video_url', 'video_type', 'video_id', 'cover_photo_id'
            ]
        ]);
        $this->seeJson([
            'id' => $video->obfuscatedId(),
            'title' => 'Woodhill forest trip',
            'date' => '1995-11-12',
            'approx_day' => 12,
            'approx_month' => 11,
            'approx_year' => 1995,
            'location' => 'Woodhill forest',
            'authorship' => 'John Smith',
            'description' => '<p>This trip was organised by Joe Blogs.</p><p>We had a very large turnout, with over 40 vehicles attending</p>',
            'video_url' => 'https://www.youtube.com/watch?v=3kjhd92387di',
            'video_type' => 'youtube',
            'video_id' => '3kjhd92387di',
            'cover_photo_id' => $coverPhoto->obfuscatedId()
        ]);
    }

    /** @test **/
    public function cannot_retrieve_a_published_video()
    {
        $video = factory(Item::class)->states('video', 'published')->create();

        app('auth')->login($video->user);

        $this->json('GET', '/drafts/videos/'.$video->obfuscatedId());

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function cannot_retrieve_someone_elses_draft_video()
    {
        $video = factory(Item::class)->states('video', 'draft')->create();

        // log in as someone else, not owner of this album
        app('auth')->login(factory(User::class)->create());

        $this->json('GET', '/drafts/videos/'.$video->obfuscatedId());

        $this->seeStatusCode(403);
    }

    /** @test **/
    public function can_retrieve_someone_elses_draft_video_as_an_editor()
    {
        $video = factory(Item::class)->states('video', 'draft')->create();

        // log in as someone else, not owner of this album
        app('auth')->login(factory(User::class)->states('editor')->create());

        $this->json('GET', '/drafts/videos/'.$video->obfuscatedId());

        $this->seeStatusCode(200);
    }

    /** @test **/
    public function can_retrieve_a_list_of_only_draft_videos()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $videoA = factory(Item::class)->states('video', 'draft')->create(['user_id' => $user->id]);
        $published = factory(Item::class)->states('video', 'published')->create(['user_id' => $user->id]);
        $videoB = factory(Item::class)->states('video', 'draft')->create(['user_id' => $user->id]);
        $otherUsersVideo = factory(Item::class)->states('video', 'draft')->create(['user_id' => $otherUser->id]);
        $videoC = factory(Item::class)->states('video', 'draft')->create(['user_id' => $user->id]);

        app('auth')->login($user);

        $this->json('GET', '/drafts/videos');

        $this->seeStatusCode(200);
        $this->seeJson();

        $this->assertCollectionEquals([
            $videoA,
            $videoB,
            $videoC
        ], $this->responseData('data'));
    }
}
