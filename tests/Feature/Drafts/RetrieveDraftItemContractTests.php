<?php

use App\Item;
use App\User;

trait RetrieveDraftItemContractTests
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
    public function cannot_retrieve_a_published_item()
    {
        $item = factory(Item::class)->states($this->itemState, 'published')->create();

        app('auth')->login($item->user);

        $this->json('GET', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId());

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function cannot_retrieve_someone_elses_draft_item()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create();

        // log in as someone else, not owner of this item
        app('auth')->login(factory(User::class)->create());

        $this->json('GET', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId());

        $this->seeStatusCode(403);
    }

    /** @test **/
    public function can_retrieve_someone_elses_draft_item_as_an_editor()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create();

        // log in as someone else, not owner of this item
        app('auth')->login(factory(User::class)->states('editor')->create());

        $this->json('GET', '/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId());

        $this->seeStatusCode(200);
    }

    /** @test **/
    public function can_retrieve_a_list_of_only_draft_items()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $itemA = factory(Item::class)->states($this->itemState, 'draft')->create(['user_id' => $user->id]);
        $published = factory(Item::class)->states($this->itemState, 'published')->create(['user_id' => $user->id]);
        $itemB = factory(Item::class)->states($this->itemState, 'draft')->create(['user_id' => $user->id]);
        $otherUsersItem = factory(Item::class)->states($this->itemState, 'draft')->create(['user_id' => $otherUser->id]);
        $itemC = factory(Item::class)->states($this->itemState, 'draft')->create(['user_id' => $user->id]);

        app('auth')->login($user);

        $this->json('GET', '/drafts/'.$this->itemUrlPath.'');

        $this->seeStatusCode(200);
        $this->seeJson();

        $this->assertCollectionEquals([
            $itemA,
            $itemB,
            $itemC
        ], $this->responseData('data'));
    }

    /** @test **/
    public function can_retrieve_a_list_of_all_draft_items_as_an_editor()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $itemA = factory(Item::class)->states($this->itemState, 'draft')->create(['user_id' => $user->id]);
        $published = factory(Item::class)->states($this->itemState, 'published')->create(['user_id' => $user->id]);
        $itemB = factory(Item::class)->states($this->itemState, 'draft')->create(['user_id' => $user->id]);
        $otherUsersItem = factory(Item::class)->states($this->itemState, 'draft')->create(['user_id' => $otherUser->id]);
        $itemC = factory(Item::class)->states($this->itemState, 'draft')->create(['user_id' => $user->id]);

        app('auth')->login(factory(User::class)->state('editor')->create());

        $this->json('GET', '/drafts/'.$this->itemUrlPath.'?user=all');

        $this->seeStatusCode(200);
        $this->seeJson();

        $this->assertCollectionEquals([
            $itemA,
            $itemB,
            $otherUsersItem,
            $itemC
        ], $this->responseData('data'));
    }

    /** @test **/
    public function can_retrieve_a_list_of_another_users_draft_items_as_an_editor()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $itemA = factory(Item::class)->states($this->itemState, 'draft')->create(['user_id' => $user->id]);
        $published = factory(Item::class)->states($this->itemState, 'published')->create(['user_id' => $user->id]);
        $itemB = factory(Item::class)->states($this->itemState, 'draft')->create(['user_id' => $user->id]);
        $otherUsersItem = factory(Item::class)->states($this->itemState, 'draft')->create(['user_id' => $otherUser->id]);
        $itemC = factory(Item::class)->states($this->itemState, 'draft')->create(['user_id' => $user->id]);

        app('auth')->login(factory(User::class)->state('editor')->create());

        $this->json('GET', '/drafts/'.$this->itemUrlPath.'?user='.$otherUser->obfuscatedId());

        $this->seeStatusCode(200);
        $this->seeJson();

        $this->assertCollectionEquals([
            $otherUsersItem
        ], $this->responseData('data'));
    }
}
