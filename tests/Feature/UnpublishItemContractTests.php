<?php

use App\Item;
use App\User;
use Illuminate\Support\Facades\Auth;

trait UnpublishItemContractTests
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
    public function can_unpublish_a_published_item()
    {
        $this->withoutExceptionHandling();

        $item = factory(Item::class)->states($this->itemState, 'published')->create();
        $this->assertFalse($item->isDraft());

        Auth::login($item->user);

        $this->json('DELETE', '/'.$this->itemUrlPath.'/'.$item->obfuscatedId());

        $this->seeStatusCode(200);
        $this->seeHeader('Location', url('/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId()));
        $this->assertTrue($item->fresh()->isDraft());
    }

    /** @test **/
    public function cannot_unpublish_someone_elses_published_item()
    {
        $item = factory(Item::class)->states($this->itemState, 'published')->create();
        $this->assertFalse($item->isDraft());

        // login as someone else (not owner of item)
        Auth::login(factory(User::class)->create());

        $this->json('DELETE', '/'.$this->itemUrlPath.'/'.$item->obfuscatedId());

        $this->seeStatusCode(403);
        $this->assertFalse($item->fresh()->isDraft());
    }

    /** @test **/
    public function can_unpublish_someone_elses_item_as_an_editor()
    {
        $item = factory(Item::class)->states($this->itemState, 'published')->create();
        $this->assertFalse($item->isDraft());

        Auth::login(factory(User::class)->state('editor')->create());

        $this->json('DELETE', '/'.$this->itemUrlPath.'/'.$item->obfuscatedId());

        $this->seeStatusCode(200);
        $this->seeHeader('Location', url('/drafts/'.$this->itemUrlPath.'/'.$item->obfuscatedId()));
        $this->assertTrue($item->fresh()->isDraft());
    }
}
