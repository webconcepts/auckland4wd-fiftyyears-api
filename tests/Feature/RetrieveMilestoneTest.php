<?php

use App\Item;
use Carbon\Carbon;
use App\IdObfuscator;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class RetrieveMilestoneTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_retrieve_a_published_milestone()
    {
        $milestone = factory(Item::class)->states('milestone', 'published')->create([
            'title' => 'Annual general meeting 1995',
            'date' => Carbon::parse('November 12, 1995'),
            'approx_day' => 12,
            'approx_month' => 11,
            'approx_year' => 1995,
            'description' => '<p>We had a very large turnout, with over 40 people attending</p>',
        ]);

        $this->json('GET', '/milestones/'.$milestone->obfuscatedId());

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
    public function cannot_retrieve_a_draft_milestone()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create();

        $this->json('GET', '/milestones/'.$milestone->obfuscatedId());

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function can_retrieve_a_list_of_only_published_milestones()
    {
        $this->withoutExceptionHandling();

        $milestoneA = factory(Item::class)->states('milestone', 'published')->create();
        $draft = factory(Item::class)->states('milestone', 'draft')->create();
        $milestoneB = factory(Item::class)->states('milestone', 'published')->create();
        $milestoneC = factory(Item::class)->states('milestone', 'published')->create();

        $this->json('GET', '/milestones');
        $content = json_decode($this->response->getContent());

        $this->seeStatusCode(200);
        $this->seeJson();

        $this->assertCollectionEquals([
            $milestoneA,
            $milestoneB,
            $milestoneC
        ], $this->responseData('data'));
    }
}
