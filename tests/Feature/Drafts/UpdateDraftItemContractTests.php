<?php

use App\Item;
use App\User;

trait UpdateDraftItemContractTests
{
    /**
     * @var string $itemState state for Item model factory
     * set in test cases using this trait
     */

    /**
     * @var string $itemUrlPath path in url for the controller related to this test
     * set in test cases using this trait
     */

    /** @test **/
    public function cannot_update_a_published_item()
    {
        $item = factory(Item::class)->states($this->itemState, 'published')->create([]);
        app('auth')->login($item->user);

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function can_update_title()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create([
            'title' => 'Original title'
        ]);
        app('auth')->login($item->user);

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('New title', $item->fresh()->title);
    }

    /** @test **/
    public function can_update_date()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create([
            'date' => '1990-01-01'
        ]);
        app('auth')->login($item->user);

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'date' => '2018-12-31'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('2018-12-31', $item->fresh()->date->toDateString());
    }

    /** @test **/
    public function date_must_be_in_correct_format()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create([
            'date' => '1990-01-01'
        ]);
        app('auth')->login($item->user);

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'date' => '2018/12/31'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('date');
    }

    /** @test **/
    public function can_update_approx_day()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create([
            'approx_day' => 2
        ]);
        app('auth')->login($item->user);

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'approx_day' => '15'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals(15, $item->fresh()->approx_day);
    }

    /** @test **/
    public function approx_day_must_be_between_1_and_31()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create();
        app('auth')->login($item->user);

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'approx_day' => '0'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_day');

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'approx_day' => '32'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_day');
    }

    /** @test **/
    public function can_update_approx_month()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create([
            'approx_month' => 4
        ]);
        app('auth')->login($item->user);

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'approx_month' => '11'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals(11, $item->fresh()->approx_month);
    }

    /** @test **/
    public function approx_month_must_be_between_1_and_12()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create();
        app('auth')->login($item->user);

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'approx_month' => '0'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_month');

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'approx_month' => '13'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_month');
    }

    /** @test **/
    public function can_update_approx_year()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create([
            'approx_year' => 1995
        ]);
        app('auth')->login($item->user);

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'approx_year' => '2001'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals(2001, $item->fresh()->approx_year);
    }

    /** @test **/
    public function approx_year_must_be_between_1969_and_2019()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create();
        app('auth')->login($item->user);

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'approx_year' => '1968'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_year');

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'approx_year' => '2020'
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('approx_year');
    }

    /** @test **/
    public function updating_approximate_date_values_updates_date_for_non_editors()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create([
            'date' => '1990-11-24',
            'approx_day' => null,
            'approx_month' => null,
            'approx_year' => null,
        ]);
        app('auth')->login($item->user);

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'approx_year' => '2001'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('2001-01-01', $item->fresh()->date->toDateString());

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'approx_month' => '5'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('2001-05-01', $item->fresh()->date->toDateString());

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'approx_day' => '13'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('2001-05-13', $item->fresh()->date->toDateString());
    }

    /** @test **/
    public function updating_approximate_date_will_not_update_date_for_an_editor()
    {
        $user = factory(User::class)->states('editor')->create();
        $item = factory(Item::class)->states($this->itemState, 'draft')->create([
            'user_id' => $user->id,
            'date' => '1990-11-24',
            'approx_day' => 24,
            'approx_month' => 11,
            'approx_year' => 1990,
        ]);
        app('auth')->login($user);

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'approx_year' => '2001',
            'approx_month' => '12',
            'approx_day' => '2'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('1990-11-24', $item->fresh()->date->toDateString());
        $this->assertEquals(2, $item->fresh()->approx_day);
        $this->assertEquals(12, $item->fresh()->approx_month);
        $this->assertEquals(2001, $item->fresh()->approx_year);
    }

    /** @test **/
    public function can_update_description()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create([
            'description' => '<p>Original description</p>'
        ]);
        app('auth')->login($item->user);

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'description' => '<p>New description</p>'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('<p>New description</p>', $item->fresh()->description);
    }

    /** @test **/
    public function will_decode_html_entities()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create([
            'title' => 'Original title'
        ]);
        app('auth')->login($item->user);

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'title' => 'One thing &amp; another thing'
        ]);

        $this->seeStatusCode(200);
        $this->assertEquals('One thing & another thing', $item->fresh()->title);
    }

    /** @test **/
    public function cannot_update_without_a_valid_field()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create();
        app('auth')->login($item->user);

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), []);

        $this->seeStatusCode(400);
    }

    /** @test **/
    public function cannot_update_when_not_logged_in()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create();

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(401);
    }

    /** @test **/
    public function cannot_update_someone_elses_item()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create();
        app('auth')->login(factory(User::class)->create());

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(403);
    }

    /** @test **/
    public function can_update_someone_elses_item_as_an_editor()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create();
        app('auth')->login(factory(User::class)->states('editor')->create());

        $this->json('PATCH', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId(), [
            'title' => 'New title'
        ]);

        $this->seeStatusCode(200);
    }
}
