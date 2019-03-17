<?php

use App\Item;
use App\User;

trait RemoveDraftItemContractTests
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
    public function can_remove_a_draft_item()
    {
        $this->withoutExceptionHandling();

        $item = factory(Item::class)->states($this->itemState, 'draft')->create();
        app('auth')->login($item->user);

        $this->json('DELETE', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId());

        $this->seeStatusCode(200);
        $this->assertTrue($item->fresh()->isRemoved());
    }

    /** @test **/
    public function cannot_remove_a_published_item()
    {
        $item = factory(Item::class)->states($this->itemState, 'published')->create();
        app('auth')->login($item->user);

        $this->json('DELETE', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId());

        $this->seeStatusCode(404);
        $this->assertFalse($item->fresh()->isRemoved());
    }

    /** @test **/
    public function cannot_remove_an_already_removed_item()
    {
        $item = factory(Item::class)->states($this->itemState, 'removed')->create();
        app('auth')->login($item->user);

        $this->json('DELETE', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId());

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function cannot_remove_someone_elses_item()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create();
        app('auth')->login(factory(User::class)->create());

        $this->json('DELETE', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId());

        $this->seeStatusCode(403);
    }

    /** @test **/
    public function cannot_remove_an_item_as_a_guest()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create();

        $this->json('DELETE', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId());

        $this->seeStatusCode(401);
    }

    /** @test **/
    public function can_remove_someone_elses_item_as_an_editor()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create();
        app('auth')->login(factory(User::class)->states('editor')->create());

        $this->json('DELETE', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId());

        $this->seeStatusCode(200);
    }
}
