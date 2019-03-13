<?php

use App\User;
use App\Item;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RemoveDraftVideoTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_remove_a_draft_video()
    {
        $this->withoutExceptionHandling();

        $video = factory(Item::class)->states('video', 'draft')->create();
        app('auth')->login($video->user);

        $this->json('DELETE', '/drafts/videos/'.$video->obfuscatedId());

        $this->seeStatusCode(200);
        $this->assertTrue($video->fresh()->isRemoved());
    }

    /** @test **/
    public function cannot_remove_a_published_video()
    {
        $video = factory(Item::class)->states('video', 'published')->create();
        app('auth')->login($video->user);

        $this->json('DELETE', '/drafts/videos/'.$video->obfuscatedId());

        $this->seeStatusCode(404);
        $this->assertFalse($video->fresh()->isRemoved());
    }

    /** @test **/
    public function cannot_remove_an_already_removed_video()
    {
        $video = factory(Item::class)->states('video', 'removed')->create();
        app('auth')->login($video->user);

        $this->json('DELETE', '/drafts/videos/'.$video->obfuscatedId());

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function cannot_remove_someone_elses_video()
    {
        $video = factory(Item::class)->states('video', 'draft')->create();
        app('auth')->login(factory(User::class)->create());

        $this->json('DELETE', '/drafts/videos/'.$video->obfuscatedId());

        $this->seeStatusCode(403);
    }

    /** @test **/
    public function cannot_remove_an_video_as_a_guest()
    {
        $video = factory(Item::class)->states('video', 'draft')->create();

        $this->json('DELETE', '/drafts/videos/'.$video->obfuscatedId());

        $this->seeStatusCode(401);
    }

    /** @test **/
    public function can_remove_someone_elses_video_as_an_editor()
    {
        $video = factory(Item::class)->states('video', 'draft')->create();
        app('auth')->login(factory(User::class)->states('editor')->create());

        $this->json('DELETE', '/drafts/videos/'.$video->obfuscatedId());

        $this->seeStatusCode(200);
    }
}
