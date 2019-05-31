<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Group;
use App\User;
use Firebase\JWT\JWT;
use Exception;

class GroupController extends Controller
{
    // List all Groups
    function listGroups(Request $request) {
        // user auth
        $token = $request->header('Authorization');
        $token = substr($token, 7-strlen($token));
        $credentials = null;
        try {
            $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        } catch(Exception $e) {
            // do nothing           
        }
        if (is_null($credentials)) {
            // user is not logged in
            $publicGroups = Group::where('is_public', true)->get();
            $privateGroups = Group::where('is_public', false)->get();
            $myGroups = [ 'error' => 'User not logged in' ];
        } else {
            // user is logged in
            $user = User::findOrFail($credentials->sub);
            $myGroups = $user->groups()->get()->map(function($group) {
                $group['is_group_admin'] = $group->pivot->is_group_admin == 1;
                return $group;
            });
            $myGroupIds = $myGroups->pluck('id');
            $publicGroups = Group::where('is_public', true)->whereNotIn('id', $myGroupIds)->get();
            $privateGroups = Group::where('is_public', false)->whereNotIn('id', $myGroupIds)->get();
        }

        $result = [
            'publicGroups' => $publicGroups, 
            'privateGroups' => $privateGroups,
            'myGroups' => $myGroups
        ];
        return response()->json($result);
    }

    // Group details
    function groupDetails(Request $request, $id) {
        $group = Group::where('id', $id)->with(['creator'])->first();
        $members = $group->members()->get()->map(function($group) {
            $group['is_group_admin'] = $group->pivot->is_group_admin == 1;
            return $group;
        });
        $group['members'] = $members;
        return response()->json($group);
    }
}
