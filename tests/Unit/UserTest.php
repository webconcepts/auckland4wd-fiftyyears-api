<?php

use App\User;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UserTest extends TestCase
{
    use DatabaseMigrations;

    /** @test **/
    public function create_a_new_user_from_an_email_address()
    {
        $userA = factory(User::class)->create();
        $userB = factory(User::class)->create();

        $this->assertEquals(2, User::count());

        $createdUser = User::firstOrCreateByEmail('jane@blogs.com', 'Jane Blogs');

        $this->assertEquals(3, User::count());
        $this->assertEquals('jane@blogs.com', $createdUser->email);
        $this->assertEquals('Jane Blogs', $createdUser->name);
    }

     /** @test **/
    public function create_a_new_user_from_an_email_address_without_a_name()
    {
        $userA = factory(User::class)->create();
        $userB = factory(User::class)->create();

        $this->assertEquals(2, User::count());

        $createdUser = User::firstOrCreateByEmail('jane@blogs.com');

        $this->assertEquals(3, User::count());
        $this->assertEquals('jane@blogs.com', $createdUser->email);
        $this->assertNull($createdUser->name);
    }

    /** @test **/
    public function find_an_existing_user_from_an_email_address()
    {
        $user = factory(User::class)->create([
            'name' => 'Joe Blogs',
            'email' => 'joe@blogs.com'
        ]);

        $this->assertEquals(1, User::count());

        $foundUser = User::firstOrCreateByEmail('joe@blogs.com');

        $this->assertEquals(1, User::count());
        $this->assertEquals($user->id, $foundUser->id);
        $this->assertTrue($foundUser->is($user));
    }

    /** @test **/
    public function user_can_be_an_editor()
    {
        $user = factory(User::class)->create();

        $this->assertFalse($user->isEditor());

        $user->editor = true;
        $user->save();

        $this->assertTrue($user->fresh()->isEditor());
    }
}
