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

// login
$app->post('api/auth/login', ['uses' => 'AuthController@authenticateByEmail']);

// get groups
$app->get('api/groups', ['uses' => 'GroupController@listGroups']);

// JWT protected routes
$app->group(['middleware' => 'jwt.auth'], function() use ($app) {

    // to ping with jwt
    $app->get('api/ping', ['uses' => 'AuthController@ping']);
    
    // upload 
    $app->post('api/image-upload', ['uses' => 'ImageController@uploadImage']);
});