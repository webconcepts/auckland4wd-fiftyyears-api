<?php

use App\Item;
use App\User;

trait AddDraftItemContractTests
{
    /**
     * @var string $itemUrlPath path in url for the controller related to this test
     * set in test cases using this trait
     */

    /** @test **/
    public function guest_cannot_create_a_item()
    {
        $this->json('POST', '/drafts/'.$this->itemUrlPath, [
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

        $this->json('POST', '/drafts/'.$this->itemUrlPath, [
            'title' => ''
        ]);

        $this->seeStatusCode(422);
        $this->assertJsonHasKey('title');
    }

    /** @test **/
    public function will_decode_html_entities_within_title()
    {
        app('auth')->login(factory(User::class)->create());

        $this->json('POST', '/drafts/'.$this->itemUrlPath, [
            'title' => 'Title with entities like &amp; and &gt;'
        ]);

        $this->seeStatusCode(201);
        $this->assertEquals('Title with entities like & and >', Item::first()->title);
        $this->seeJson(['title' => 'Title with entities like & and >']);
    }
}
