<?php

use App\User;
use App\Item;
use App\IdObfuscator;
use Tymon\JWTAuth\JWT;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AddDraftMilestoneTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function can_create_a_valid_milestone()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create([
            'email' => 'jane@blogs.com'
        ]);
        app('auth')->login($user);

        $this->json('POST', '/drafts/milestones', [
            'title' => 'Waiuku trip organised'
        ]);

        $this->seeStatusCode(201);
        $this->seeJsonStructure([
            'data' => [
                'id', 'title', 'date', 'approx_day', 'approx_month', 'approx_year', 'description'
            ]
        ]);

        tap(Item::first(), function ($milestone) use ($user) {
            $this->seeHeader('Location', url('/drafts/milestones/'.$milestone->obfuscatedId()));

            $this->seeJson([
                'id' => $milestone->obfuscatedId(),
                'title' => 'Waiuku trip organised',
                'date' => null,
                'approx_day' => null,
                'approx_month' => null,
                'approx_year' => null,
                'description' => null,
            ]);

            $this->assertEquals(Item::MILESTONE, $milestone->type);
            $this->assertFalse($milestone->isPublished());
            $this->assertEquals('Waiuku trip organised', $milestone->title);
            $this->assertEquals($user->id, $milestone->user->id);
            $this->assertEquals('jane@blogs.com', $milestone->user->email);
        });
    }

    /** @test **/
    public function guest_cannot_create_a_milestone()
    {
        $this->json('POST', '/drafts/milestones', [
            'title' => 'Woodhill forest trip',
            'user' => (object) [
                'name' => 'Joe Blogs',
                'email' => 'joe@blogs.com'
            ]
        ]);

        $this->seeStatusCode(401);

        $this->assertEquals(0, Item::count());
    }

    /** @test **/
    public function title_is_required()
    {
        app('auth')->login(factory(User::class)->create());

        $this->json('POST', '/drafts/milestones', [
            'title' => ''
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('title');
    }
}
