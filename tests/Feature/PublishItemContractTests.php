<?php

use App\Item;
use App\User;
use Illuminate\Support\Facades\Auth;

trait PublishItemContractTests
{
    /**
     * @var string $itemState state for Item model factory
     * set in test cases using this trait
     */

    /**
     * @var string $this->itemUrlPath path in url for the controller related to this test
     * set in test cases using this trait
     */

    /** @test **/
    public function can_publish_a_draft_item()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create([
            'published_at' => null
        ]);
        $this->assertFalse($item->isPublished());

        Auth::login($item->user);

        $this->json('POST', '/'.$this->itemUrlPath, [
            'id' => $item->obfuscatedId()
        ]);

        $this->seeStatusCode(201);
        $this->seeHeader('Location', url('/'.$this->itemUrlPath.'/'.$item->obfuscatedId()));
        $this->assertTrue($item->fresh()->isPublished());
    }

    /** @test **/
    public function cannot_publish_someone_elses_item()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create([
            'published_at' => null
        ]);
        $this->assertFalse($item->isPublished());

        // login as someone else (not owner of item)
        Auth::login(factory(User::class)->create());

        $this->json('POST', '/'.$this->itemUrlPath, [
            'id' => $item->obfuscatedId()
        ]);

        $this->seeStatusCode(403);
        $this->assertFalse($item->fresh()->isPublished());
    }

    /** @test **/
    public function can_publish_someone_elses_item_as_an_editor()
    {
        $item = factory(Item::class)->states($this->itemState, 'draft')->create([
            'published_at' => null
        ]);
        $this->assertFalse($item->isPublished());

        Auth::login(factory(User::class)->state('editor')->create());

        $this->json('POST', '/'.$this->itemUrlPath, [
            'id' => $item->obfuscatedId()
        ]);

        $this->seeStatusCode(201);
        $this->seeHeader('Location', url('/'.$this->itemUrlPath.'/'.$item->obfuscatedId()));
        $this->assertTrue($item->fresh()->isPublished());
    }

    /** @test **/
    public function id_is_required()
    {
        Auth::login(factory(User::class)->create());

        $this->json('POST', '/'.$this->itemUrlPath, []);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('id');
    }
}
