<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->post(
    'api/auth/login', [ 'uses' => 'AuthController@authenticateByEmail']
);

// JWT protected routes
$app->group(['middleware' => 'jwt.auth'], function() use ($app) {

    // get all users, TODO: this is only an experiment
    $app->get('api/users', function() {
        $users = \App\User::all();
        return response()->json($users);
    });
});