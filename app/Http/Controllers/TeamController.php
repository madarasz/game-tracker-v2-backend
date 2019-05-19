<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Team;

class TeamController extends Controller
{
    // List all public Teams
    function listPublicTeams() {
        $teams = Team::where('public', true)->get();
        // remove public field
        $teams->transform(function($i) {
            unset($i->public);
            return $i;
        });
        return response()->json($teams);
    }
}
