<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $newToken = null;

        if ($this->auth->guard($guard)->guest()) {
            // attempt refresh
            try {
                if (!$newToken = $this->auth->refresh()) {
                    throw new Exception();
                }
            } catch (Exception $e) {
                return response('Unauthorized.', 401);
            }
        }

        $response = $next($request);

        // if token was refreshed, add to response in header
        if ($newToken) {
            return $response->header('x-access_token', $newToken);
        } else {
            return $response;
        }
    }
}
