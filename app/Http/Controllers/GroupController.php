<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Group;

class GroupController extends Controller
{
    // List all Groups
    function listGroups(Request $request) {
        $publicGroups = Group::where('is_public', true)->get();
        $privateGroups = Group::where('is_public', false)->get();
        $myGroups = [ 'error' => 'User not logged in' ];
        $result = [
            'publicGroups' => $publicGroups, 
            'privateGroups' => $privateGroups,
            'myGroups' => $myGroups
        ];
        return response()->json($result);
    }
}
