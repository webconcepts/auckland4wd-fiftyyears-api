<?php

use App\User;
use Illuminate\Support\Carbon;
use Tymon\JWTAuth\Http\Parser\AuthHeaders;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AutoRefreshAuthTokenTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Mock JWT token parser, because it doesnt work requests from tests
     *
     * @param string $jwtToken related to test request
     */
    protected function mockJwtParserToReturn($jwtToken)
    {
        $this->app['tymon.jwt.parser']->setChain([
            Mockery::mock(AuthHeaders::class)
                ->shouldReceive('parse')
                ->andReturn($jwtToken)
                ->getMock()
        ]);
    }

    /** @test **/
    public function token_automatically_refreshed_and_returned_in_response_header_when_token_had_expired()
    {
        Carbon::setTestNow('-1 day');

        $user = factory(User::class)->create();
        $expiredToken = app('auth')->fromSubject($user);

        Carbon::setTestNow('+1 day');

        $this->mockJwtParserToReturn($expiredToken);

        $this->json('POST', '/drafts/photo-albums', [
            'title' => 'Woodhill forest trip'
        ]);

        $this->seeStatusCode(201);

        $newToken = $this->response->headers->get('x-access_token');
        $this->assertNotEmpty($newToken);
        $this->assertNotSame($newToken, $expiredToken);
    }

    /** @test **/
    public function token_not_in_response_header_when_token_did_not_need_to_be_refreshed()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $currentToken = app('auth')->fromSubject($user);

        $this->mockJwtParserToReturn($currentToken);

        $this->json('POST', '/drafts/photo-albums', [
            'title' => 'Woodhill forest trip'
        ]);

        $this->seeStatusCode(201);
        $this->assertFalse($this->response->headers->has('x-access_token'));
    }
}
