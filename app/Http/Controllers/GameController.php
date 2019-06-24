<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Game;
use App\Group;
use Carbon\Carbon;

class GameController extends Controller
{
    // create new Game / new Group-Game 
    function addGame(Request $request) {
        // auth check
        if (!$this->authCheck($request)) {
            return response()->json(['error' => 'not authorised'], 403);
        }

        $game = Game::find($request->bgg_id);
        
        // create new game entry if does not exist
        if (is_null($game)) {
            $game = Game::create([
                'id' => $request->bgg_id,
                'name' => $request->name,
                'designers' => $request->designers,
                'thumbnail' => $request->thumbnail,
                'year' => $request->year,
                'type' => $request->type,
                'created_by' => $request->user->id
            ]);
        }
        
        // group-game entry
        if (\DB::table('group_game')->where('game_id', $request->bgg_id)
            ->where('group_id', $request->group_id)->exists()) {
                // game already added
                return response()->json(['error' => 'Game is already added to group'], 405);
        } else {
            //create new group-game entry
            $date = new \DateTime();
            $game->groups()->attach($request->group_id, [
                'created_by' => $request->user->id,
                'created_at' => Carbon::now(),
                'game_id' => $request->bgg_id
            ]);
        }

        return response()->json($game);
    }

    // remove Group-Game
    function deleteGame(Request $request) {
        // auth check
        if (!$this->authCheck($request)) {
            return response()->json(['error' => 'not authorised'], 403);
        }

        // delete entry
        \DB::table('group_game')
            ->where('group_id', $request->group_id)
            ->where('game_id', $request->game_id)
            ->delete();

        return response()->json(['message' => 'Game deleted from group']);
    }

    // get game details
    function gameDetails(Request $request, $groupid, $gameid) {
        $group = Group::findOrFail($groupid);
        $members = $group->members()->get();

        // auth check
        if (!$group->is_public && (!$request->has('user') || !$request->user->is_admin) && !$members->contains($request->user)) {
            return response()->json(['error' => 'not authorised'], 403);
        }

        $game = Game::findOrFail($gameid);
        $game['sessions'] = $game->sessions($groupid)->get();

        return response()->json($game);
    }

    private function authCheck($request) {
        return $request->has('group_id') && ($request->user->is_admin || 
            \DB::table('group_user')->where('group_id', $request->group_id)
                ->where('user_id', $request->user->id)->exists());
    }
}
