<?php

use App\User;
use App\Item;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RemoveDraftMilestoneTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_remove_a_draft_milestone()
    {
        $this->withoutExceptionHandling();

        $milestone = factory(Item::class)->states('milestone', 'draft')->create();
        app('auth')->login($milestone->user);

        $this->json('DELETE', '/drafts/milestones/'.$milestone->obfuscatedId());

        $this->seeStatusCode(200);
        $this->assertTrue($milestone->fresh()->isRemoved());
    }

    /** @test **/
    public function cannot_remove_a_published_milestone()
    {
        $milestone = factory(Item::class)->states('milestone', 'published')->create();
        app('auth')->login($milestone->user);

        $this->json('DELETE', '/drafts/milestones/'.$milestone->obfuscatedId());

        $this->seeStatusCode(404);
        $this->assertFalse($milestone->fresh()->isRemoved());
    }

    /** @test **/
    public function cannot_remove_an_already_removed_milestone()
    {
        $milestone = factory(Item::class)->states('milestone', 'removed')->create();
        app('auth')->login($milestone->user);

        $this->json('DELETE', '/drafts/milestones/'.$milestone->obfuscatedId());

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function cannot_remove_someone_elses_milestone()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create();
        app('auth')->login(factory(User::class)->create());

        $this->json('DELETE', '/drafts/milestones/'.$milestone->obfuscatedId());

        $this->seeStatusCode(403);
    }

    /** @test **/
    public function cannot_remove_an_milestone_as_a_guest()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create();

        $this->json('DELETE', '/drafts/milestones/'.$milestone->obfuscatedId());

        $this->seeStatusCode(401);
    }

    /** @test **/
    public function can_remove_someone_elses_milestone_as_an_editor()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create();
        app('auth')->login(factory(User::class)->states('editor')->create());

        $this->json('DELETE', '/drafts/milestones/'.$milestone->obfuscatedId());

        $this->seeStatusCode(200);
    }
}
