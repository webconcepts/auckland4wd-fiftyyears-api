<?php

use App\Item;
use App\User;
use Carbon\Carbon;
use App\IdObfuscator;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RetrieveDraftMilestoneTest extends TestCase
{
    use DatabaseMigrations, RetrieveDraftItemContractTests;

    protected $itemState = 'milestone';

    protected $itemUrlPath = 'milestones';

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
}
