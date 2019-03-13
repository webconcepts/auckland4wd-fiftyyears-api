<?php

use App\Item;
use App\User;
use App\Video\VideoInfo;
use App\Video\FakeVideoInfo;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UpdateDraftMilestoneTest extends TestCase
{
    use DatabaseMigrations;

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

    /** @test **/
    public function cannot_update_a_published_album()
    {
        $milestone = factory(Item::class)->states('milestone', 'published')->create([]);
        app('auth')->login($milestone->user);

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function can_update_title()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create([
            'title' => 'Original title'
        ]);
        app('auth')->login($milestone->user);

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('New title', $milestone->fresh()->title);
    }

    /** @test **/
    public function can_update_date()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create([
            'date' => '1990-01-01'
        ]);
        app('auth')->login($milestone->user);

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'date' => '2018-12-31'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('2018-12-31', $milestone->fresh()->date->toDateString());
    }

    /** @test **/
    public function date_must_be_in_correct_format()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create([
            'date' => '1990-01-01'
        ]);
        app('auth')->login($milestone->user);

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'date' => '2018/12/31'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('date');
    }

    /** @test **/
    public function can_update_approx_day()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create([
            'approx_day' => 2
        ]);
        app('auth')->login($milestone->user);

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'approx_day' => '15'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals(15, $milestone->fresh()->approx_day);
    }

    /** @test **/
    public function approx_day_must_be_between_1_and_31()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create();
        app('auth')->login($milestone->user);

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'approx_day' => '0'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_day');

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'approx_day' => '32'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_day');
    }

    /** @test **/
    public function can_update_approx_month()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create([
            'approx_month' => 4
        ]);
        app('auth')->login($milestone->user);

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'approx_month' => '11'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals(11, $milestone->fresh()->approx_month);
    }

    /** @test **/
    public function approx_month_must_be_between_1_and_12()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create();
        app('auth')->login($milestone->user);

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'approx_month' => '0'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_month');

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'approx_month' => '13'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_month');
    }

    /** @test **/
    public function can_update_approx_year()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create([
            'approx_year' => 1995
        ]);
        app('auth')->login($milestone->user);

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'approx_year' => '2001'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals(2001, $milestone->fresh()->approx_year);
    }

    /** @test **/
    public function approx_year_must_be_between_1969_and_2019()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create();
        app('auth')->login($milestone->user);

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'approx_year' => '1968'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_year');

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'approx_year' => '2020'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_year');
    }

    /** @test **/
    public function updating_approximate_date_values_updates_date_for_non_editors()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create([
            'date' => '1990-11-24',
            'approx_day' => null,
            'approx_month' => null,
            'approx_year' => null,
        ]);
        app('auth')->login($milestone->user);

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'approx_year' => '2001'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('2001-01-01', $milestone->fresh()->date->toDateString());

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'approx_month' => '5'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('2001-05-01', $milestone->fresh()->date->toDateString());

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'approx_day' => '13'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('2001-05-13', $milestone->fresh()->date->toDateString());
    }

    /** @test **/
    public function updating_approximate_date_will_not_update_date_for_an_editor()
    {
        $user = factory(User::class)->states('editor')->create();
        $milestone = factory(Item::class)->states('milestone', 'draft')->create([
            'user_id' => $user->id,
            'date' => '1990-11-24',
            'approx_day' => 24,
            'approx_month' => 11,
            'approx_year' => 1990,
        ]);
        app('auth')->login($user);

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'approx_year' => '2001',
            'approx_month' => '12',
            'approx_day' => '2'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('1990-11-24', $milestone->fresh()->date->toDateString());
        $this->assertEquals(2, $milestone->fresh()->approx_day);
        $this->assertEquals(12, $milestone->fresh()->approx_month);
        $this->assertEquals(2001, $milestone->fresh()->approx_year);
    }

    /** @test **/
    public function can_update_description()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create([
            'description' => '<p>Original description</p>'
        ]);
        app('auth')->login($milestone->user);

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'description' => '<p>New description</p>'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('<p>New description</p>', $milestone->fresh()->description);
    }

    /** @test **/
    public function cannot_update_without_a_valid_field()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create();
        app('auth')->login($milestone->user);

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), []);

        $this->seeStatusCode(400);
    }

    /** @test **/
    public function cannot_update_when_not_logged_in()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create();

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(401);
    }

    /** @test **/
    public function cannot_update_someone_elses_album()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create();
        app('auth')->login(factory(User::class)->create());

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(403);
    }

    /** @test **/
    public function can_update_someone_elses_album_as_an_editor()
    {
        $milestone = factory(Item::class)->states('milestone', 'draft')->create();
        app('auth')->login(factory(User::class)->states('editor')->create());

        $this->json('PATCH', '/drafts/milestones/'.$milestone->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(200);
    }
}
