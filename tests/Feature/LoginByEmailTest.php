<?php

use App\User;
use Tymon\JWTAuth\Factory;
use Tymon\JWTAuth\JWTGuard;
use Illuminate\Support\Carbon;
use App\Mail\VerificationEmail;
use App\VerificationCodeGenerator;
use Illuminate\Support\Facades\Mail;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class LoginByEmailTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Replaces JWTGuard in AuthManager with a mock
     *
     * @return Mockery\Mock so you can set your expectations
     */
    protected function mockJWTGuard()
    {
        $mock = Mockery::mock(JWTGuard::class)
            ->shouldReceive('factory')
            ->andReturn(
                Mockery::mock(Factory::class)
                    ->shouldReceive('getTTL')
                    ->andReturn(120)
                    ->getMock()
            )
            ->getMock();

        app('auth')->extend('jwt', function ($app, $name, array $config) use ($mock) {
            $app->refresh('request', $mock, 'setRequest');
            return $mock;
        });

        return $mock;
    }

    /** @test **/
    public function create_a_new_user_from_an_email_address()
    {
        $this->withoutExceptionHandling();

        Mail::fake();

        $this->app->instance(
            VerificationCodeGenerator::class,
            Mockery::mock(VerificationCodeGenerator::class)
                ->shouldReceive('generate')
                ->andReturn('TEST_VERIFICATION_CODE')
                ->getMock()
        );

        $this->mockJWTGuard()
            ->shouldReceive('login')
            ->andReturn('TEST_JWT_TOKEN');

        $this->json('POST', '/auth/user', ['email' => 'joe@blogs.com']);

        $this->seeStatusCode(201);

        $this->seeJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
            'user' => ['email']
        ]);
        $this->seeJson(['access_token' => 'TEST_JWT_TOKEN']);
        $this->seeJson(['expires_in' => 120 * 60]);
        $this->seeJson(['email' => 'joe@blogs.com']);

        $user = User::where('email', 'joe@blogs.com')->first();
        $this->assertNotNull($user);
    }

    /** @test **/
    public function verification_code_sent_via_email()
    {
        Mail::fake();

        $this->app->instance(
            VerificationCodeGenerator::class,
            Mockery::mock(VerificationCodeGenerator::class)
                ->shouldReceive('generate')
                ->andReturn('TEST_VERIFICATION_CODE')
                ->getMock()
        );

        $this->json('POST', '/auth/verification', ['email' => 'joe@blogs.com']);

        $this->seeStatusCode(201);
        $this->seeJson([
            'email' => 'joe@blogs.com'
        ]);

        $user = User::where('email', 'joe@blogs.com')->first();

        $this->assertNotNull($user);
        $this->assertNotNull($user->verification_expires_at);
        $this->assertEquals('TEST_VERIFICATION_CODE', $user->verification_code);
        $this->assertTrue(Carbon::parse($user->verification_expires_at)->greaterThan(Carbon::parse('+5 minutes')));
        $this->assertTrue(Carbon::parse($user->verification_expires_at)->lessThan(Carbon::parse('+20 minutes')));

        Mail::assertSent(VerificationEmail::class, function ($email) use ($user) {
            return $email->hasTo('joe@blogs.com')
                && $email->user->is($user);
        });
    }

    /** @test **/
    public function verification_code_can_only_be_generated_for_a_valid_email()
    {
        $this->json('POST', '/auth/verification', ['email' => 'this_is_not_an_email']);

        $this->seeStatusCode(422);

        $this->json('POST', '/auth/verification');

        $this->seeStatusCode(422);
    }

    /** @test **/
    public function create_auth_token_from_verification_code()
    {
        $this->mockJWTGuard()
            ->shouldReceive('login')
            ->andReturn('TEST_JWT_TOKEN');

        $user = factory(User::class)->create([
            'email' => 'joe@blogs.com',
            'verification_code' => 'TEST_VERIFICATION_CODE',
            'verification_expires_at' => Carbon::parse('+7 minutes'),
        ]);

        $this->json('POST', '/auth/token', [
            'verification_code' => 'TEST_VERIFICATION_CODE'
        ]);

        $this->seeStatusCode(201);
        $this->seeJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
            'user' => ['email']
        ]);
        $this->seeJson(['access_token' => 'TEST_JWT_TOKEN']);
        $this->seeJson(['expires_in' => 120 * 60]);
        $this->seeJson(['email' => 'joe@blogs.com']);

        tap($user->fresh(), function ($user) {
            $this->assertNull($user->verification_code);
            $this->assertNull($user->verification_expires_at);
        });
    }

    /** @test **/
    public function cannot_create_auth_token_from_invalid_verification_code()
    {
        $user = factory(User::class)->create([
            'email' => 'joe@blogs.com',
            'verification_code' => 'shd8s75skjsd68sud67',
            'verification_expires_at' => Carbon::parse('+7 minutes'),
        ]);

        $this->json('POST', '/auth/token', [
            'verification_code' => 'INVALID_VERIFICATION_CODE'
        ]);

        $this->seeStatusCode(404);
    }

    /** @test **/
    public function cannot_create_auth_token_from_expired_verification_code()
    {
        Mail::fake();

        $user = factory(User::class)->create([
            'email' => 'joe@blogs.com',
            'verification_code' => 'TEST_VERIFICATION_CODE',
            'verification_expires_at' => Carbon::parse('-3 minutes'),
        ]);

        $this->json('POST', '/auth/token', [
            'verification_code' => 'TEST_VERIFICATION_CODE'
        ]);

        $this->seeStatusCode(410);

        // new verification code created
        tap($user->fresh(), function ($user) {
            $this->assertNotNull($user->verification_code);
            $this->assertNotEquals('TEST_VERIFICATION_CODE', $user->verification_code);
            $this->assertNotNull($user->verification_expires_at);
            $this->assertTrue(Carbon::parse($user->verification_expires_at)->greaterThan(Carbon::parse('+5 minutes')));
            $this->assertTrue(Carbon::parse($user->verification_expires_at)->lessThan(Carbon::parse('+20 minutes')));
        });

        Mail::assertSent(VerificationEmail::class, function ($email) use ($user) {
            return $email->hasTo('joe@blogs.com')
                && $email->user->is($user);
        });
    }

    /** @test **/
    public function cannot_create_auth_token_without_giving_a_verification_code()
    {
        $user = factory(User::class)->create([
            'email' => 'joe@blogs.com',
            'verification_code' => 'shd8s75skjsd68sud67',
            'verification_expires_at' => Carbon::parse('+7 minutes'),
        ]);

        $this->json('POST', '/auth/token', []);

        $this->seeStatusCode(422);
    }

    /** @test **/
    public function can_refresh_current_auth_token()
    {
        $user = factory(User::class)->create([
            'email' => 'joe@blogs.com'
        ]);
        $token = app('auth')->login($user);

        $this->json('PATCH', '/auth/token', [], [
            'Authorization' => 'Bearer '.$token
        ]);

        $this->seeStatusCode(201);
        $this->seeJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
            'user' => ['email']
        ]);
        $this->seeJson(['email' => 'joe@blogs.com']);

        $this->assertNotNull($this->responseData('access_token'));
        $this->assertNotEquals($token, $this->responseData('access_token'));
    }

    /** @test **/
    public function cannot_refresh_invalid_auth_token()
    {
        $user = factory(User::class)->create([
            'email' => 'joe@blogs.com'
        ]);

        $this->json('PATCH', '/auth/token', [], [
            'Authorization' => 'Bearer NOTAVALIDTOKEN'
        ]);

        $this->seeStatusCode(401);
    }
}
