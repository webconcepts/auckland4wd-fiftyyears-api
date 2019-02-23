<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Create a new user
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        // check user for email doesnt already exist
        if (User::where('email', $request->input('email'))->count()) {
            abort(409, 'A user with that email already exists');
        }

        $user = User::firstOrCreateByEmail($request->input('email'));
        $token = app('auth')->login($user);

        return $this->tokenResponse($token, $user, 201);
    }

    /**
     * Email a verification code to the user to check they are who they say
     * they are
     */
    public function verify(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        $user = User::firstOrCreateByEmail($request->input('email'));

        $user->verify(); // creates and emails verification code

        return response(['email' => $user->email], 201);
    }

    /**
     * Create a new access token, from a given verification code
     */
    public function generateToken(Request $request)
    {
        $this->validate($request, [
            'verification_code' => 'required',
        ]);

        $user = User::where('verification_code', $request->input('verification_code'))
            ->firstOrFail();

        // handle expired verifcation
        if ($user->verification_expires_at->isPast()) {
            // create and email new verification code
            $user->verify();

            abort(410, 'Verification code has expired');
        }

        $token = app('auth')->login($user);

        $user->verified();

        return $this->tokenResponse($token, $user, 201);
    }

    /**
     * Refresh an access token
     */
    public function refreshToken(Request $request)
    {
        $token = app('auth')->refresh();

        return $this->tokenResponse($token, app('auth')->user(), 201);
    }

    protected function tokenResponse($token, User $user, $statusCode)
    {
        return response([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => app('auth')->factory()->getTTL() * 60,
            'user' => $user->toArray(),
        ], $statusCode);
    }
}
