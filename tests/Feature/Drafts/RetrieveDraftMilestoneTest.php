<?php

use App\Item;
use App\User;
use Carbon\Carbon;
use App\IdObfuscator;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RetrieveDraftMilestoneTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_retrieve_a_draft_milestone()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create([
            'title' => 'Annual general meeting 1995',
            'date' => Carbon::parse('November 12, 1995'),
            'approx_day' => 12,
            'approx_month' => 11,
            'approx_year' => 1995,
            'description' => '<p>We had a very large turnout, with over 40 people attending</p>',
        ]);

        app('auth')->login($milestone->user);

        $this->json('GET', '/drafts/milestones/'.$milestone->obfuscatedId());

        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                'id', 'title', 'date', 'approx_day', 'approx_month', 'approx_year', 'description'
            ]
        ]);
        $this->seeJson([
            'id' => $milestone->obfuscatedId(),
            'title' => 'Annual general meeting 1995',
            'date' => '1995-11-12',
            'approx_day' => 12,
            'approx_month' => 11,
            'approx_year' => 1995,
            'description' => '<p>We had a very large turnout, with over 40 people attending</p>',
        ]);
    }

    /** @test **/
    public function cannot_retrieve_a_published_milestone()
    {
        $milestone = factory(Item::class)->states('milestone', 'published')->create();

        app('auth')->login($milestone->user);

        $this->json('GET', '/drafts/milestones/'.$milestone->obfuscatedId());

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function cannot_retrieve_someone_elses_draft_milestone()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create();

        // log in as someone else, not owner of this album
        app('auth')->login(factory(User::class)->create());

        $this->json('GET', '/drafts/milestones/'.$milestone->obfuscatedId());

        $this->seeStatusCode(403);
    }

    /** @test **/
    public function can_retrieve_someone_elses_draft_milestone_as_an_editor()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create();

        // log in as someone else, not owner of this album
        app('auth')->login(factory(User::class)->states('editor')->create());

        $this->json('GET', '/drafts/milestones/'.$milestone->obfuscatedId());

        $this->seeStatusCode(200);
    }

    /** @test **/
    public function can_retrieve_a_list_of_only_draft_milestones()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $milestoneA = factory(Item::class)->states('milestone', 'draft')->create(['user_id' => $user->id]);
        $published = factory(Item::class)->states('milestone', 'published')->create(['user_id' => $user->id]);
        $milestoneB = factory(Item::class)->states('milestone', 'draft')->create(['user_id' => $user->id]);
        $otherUsersMilestone = factory(Item::class)->states('milestone', 'draft')->create(['user_id' => $otherUser->id]);
        $milestoneC = factory(Item::class)->states('milestone', 'draft')->create(['user_id' => $user->id]);

        app('auth')->login($user);

        $this->json('GET', '/drafts/milestones');

        $this->seeStatusCode(200);
        $this->seeJson();

        $this->assertCollectionEquals([
            $milestoneA,
            $milestoneB,
            $milestoneC
        ], $this->responseData('data'));
    }
}
