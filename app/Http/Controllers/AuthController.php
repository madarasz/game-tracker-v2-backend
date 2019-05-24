<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Validator;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    private $request;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    // empty endpoint that the user can ping to verify JWT token and its expiration
    public function ping() {
        return response()->json([
            'message' => 'Your JWT token was accepted.'
        ], 200);
    }

    protected function jwt(User $user) {
        $payload = [
            'iss' => "gametracker",
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + 60*60*24*30,  // 30 days
            'is_admin' => $user->is_admin ? 1 : 0
        ];

        return JWT::encode($payload, env('JWT_SECRET'));
    }

    public function authenticateByEmail(User $user) {
        $this->validate($this->request, [
            'email'     => 'required|email',
            'password'  => 'required'
        ]);

        // find by email
        $user = User::where('email', $this->request->input('email'))->first();
        if (!$user) {
            return response()->json([
                'error' => 'Email or password is wrong.'
            ], 400);
        }

        // Verify the password and generate the token
        if (Hash::check($this->request->input('password'), $user->password)) {
            return response()->json([
                'token' => $this->jwt($user),
                'userId' => $user->id,
                'isAdmin' => $user->is_admin,
                'userName' => $user->name,
                'imageFile' => $user->image->filename
            ], 200);
        }

        // Bad Request response
        return response()->json([
            'error' => 'Email or password is wrong.'
        ], 400);
    }
}
