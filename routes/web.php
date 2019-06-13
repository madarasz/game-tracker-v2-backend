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
// get group details
$app->get('api/groups/{id}', ['uses' => 'GroupController@groupDetails']);
// get game details
$app->get('api/groups/{groupid}/games/{gameid}', ['uses' => 'GameController@gameDetails']);

// JWT protected routes
$app->group(['middleware' => 'jwt.auth'], function() use ($app) {

    // to ping with jwt
    $app->get('api/auth/ping', ['uses' => 'AuthController@ping']);
    
    // upload image
    $app->post('api/images', ['uses' => 'ImageController@uploadImage']);

    // remove image 
    $app->delete('api/images', ['uses' => 'ImageController@removeImage']);

    // add game
    $app->post('api/games', ['uses' => 'GameController@addGame']);

    // delete game
    $app->delete('api/games', ['uses' => 'GameController@deleteGame']);

    // update group details
    $app->put('api/groups/{id}', ['uses' => 'GroupController@updateGroup']);
});