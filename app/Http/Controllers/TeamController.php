<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Team;

class TeamController extends Controller
{
    // List all Teams
    function listTeams(Request $request) {
        $publicTeams = Team::where('is_public', true)->get();
        $privateTeams = Team::where('is_public', false)->get();
        $myTeams = [ 'error' => 'User not logged in' ];
        $result = [
            'publicTeams' => $publicTeams, 
            'privateTeams' => $privateTeams,
            'myTeams' => $myTeams
        ];
        return response()->json($result);
    }
}
