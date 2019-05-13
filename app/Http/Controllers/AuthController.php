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

    protected function jwt(User $user) {
        $payload = [
            'iss' => "gametracker",
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + 10*60
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
                'error' => 'Email does not exist.'
            ], 400);
        }

        // Verify the password and generate the token
        if (Hash::check($this->request->input('password'), $user->password)) {
            return response()->json([
                'token' => $this->jwt($user)
            ], 200);
        }

        // Bad Request response
        return response()->json([
            'error' => 'Email or password is wrong.'
        ], 400);
    }
}
