<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Session;
use App\Group;

class SessionController extends Controller
{
    // add game session
    function addSession(Request $request) {
        // validation
        $this->validate($request, [
            'group_id' => 'required',
            'game_id' => 'required'
        ]);

        // auth
        if (!$this->createAuthCheck($request)) {
            return response()->json([
                'error' => 'Not authorized to add session'
            ], 403);
        }

        // create session
        $session = Session::create([
            'group_id' => $request->group_id,
            'game_id' => $request->game_id,
            'created_by' => $request->user->id,
            'notes' => $request->notes,
            'place' => $request->place,
            'date' => $request->date,
            'concluded' => false
        ]);

        return response()->json($session);
    }

    // update game session
    function updateSession(Request $request, $id) {
        $session = Session::findOrFail($id);

        // auth
        if (!$this->modifyAuthCheck($request, $session)) {
            return response()->json([
                'error' => 'Not authorized to add session'
            ], 403);
        }

        $session->update($request->all());

        return response()->json($session);
    }

    // allowed for group members and site admins
    private function createAuthCheck(Request $request) {
        $group = Group::findOrFail($request->group_id);
        $members = $group->members()->get();
        return $request->user->is_admin || $members->contains($request->user);
    }

    // allowed for site admins, group admins and creator of session
    private function modifyAuthCheck(Request $request, $session) {
        return $request->user->is_admin || $session->created_by == $request->user->id || 
            \DB::table('group_user')->where('user_id', $request->user->id)->where('group_id', $session->group_id)->where('is_group_admin', true)->exists();
    }

}
