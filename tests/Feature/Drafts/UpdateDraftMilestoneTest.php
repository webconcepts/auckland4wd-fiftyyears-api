<?php

use App\Item;
use App\User;
use App\Video\VideoInfo;
use App\Video\FakeVideoInfo;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UpdateDraftMilestoneTest extends TestCase
{
    use DatabaseMigrations, UpdateDraftItemContractTests;

    protected $itemState = 'milestone';

    protected $itemUrlPath = 'milestones';

    /** @test **/
    public function can_update_a_draft_milestone()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create([
            'title' => 'Original title',
            'date' => '1990-01-01',
            'approx_day' => 1,
            'approx_month' => 1,
            'approx_year' => 1990,
            'description' => '<p>Original description</p>',
        ]);
        app('auth')->login($milestone->user);

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'title' => 'New title',
            'date' => '2018-12-31',
            'approx_day' => '31',
            'approx_month' => '12',
            'approx_year' => '2018',
            'description' => '<p>New description</p>',
        ]);

        $this->seeStatusCode(200);

        $this->seeJsonStructure([
            'data' => [
                'id', 'title', 'date', 'approx_day', 'approx_month', 'approx_year', 'description'
            ]
        ]);
        $this->seeJson([
            'id' => $milestone->obfuscatedId(),
            'title' => 'New title',
            'date' => '2018-12-31',
            'approx_day' => 31,
            'approx_month' => 12,
            'approx_year' => 2018,
            'description' => '<p>New description</p>',
        ]);

        tap($milestone->fresh(), function ($milestone) {
            $this->assertEquals('New title', $milestone->title);
            $this->assertEquals('2018-12-31', $milestone->date->toDateString());
            $this->assertEquals(31, $milestone->approx_day);
            $this->assertEquals(12, $milestone->approx_month);
            $this->assertEquals(2018, $milestone->approx_year);
            $this->assertEquals('<p>New description</p>', $milestone->description);
        });
    }
}
