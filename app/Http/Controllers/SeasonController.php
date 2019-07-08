<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Season;
use App\Session;

class SeasonController extends Controller
{
    // add season
    public function addSeason(Request $request) {
        // validation
        $this->validate($request, [
            'group_id' => 'required',
            'game_id' => 'required',
            'start_date' => 'required|before:end_date'
        ]);
        // auth
        if (!$this->authSeason($request, $request->group_id)) {
            return response()->json([
                'error' => 'Not authorized to add season'
            ], 403);
        }
        // overlap check
        if ($this->doesOverlapExist($request)) {
            return response()->json([
                'error' => 'Overlapping season already exists'
            ], 400);
        }

        // create season
        $request->merge(['created_by' => $request->user->id]);
        $season = Season::create($request->all());
        
        $this->updateSessionsOnCreatingSeason($season);

        return response()->json($season);
    }

    // modify season
    public function updateSeason(Request $request, $id) {
        $season = Season::findOrFail($id);
        // validation
        $this->validate($request, [
            'group_id' => 'required',
            'game_id' => 'required',
            'start_date' => 'required|before:end_date'
        ]);
        // auth
        if (!$this->authSeason($request, $season->group_id)) {
            return response()->json([
                'error' => 'Not authorized to add season'
            ], 403);
        }
        // overlap check
        if ($this->doesOverlapExist($request, $id)) {
            return response()->json([
                'error' => 'Overlapping season already exists'
            ], 400);
        }

        $this->updateSessionsOnDeletingSeason($season);

        $request->merge(['created_by' => $request->user->id]); // modify created_by
        $season->update($request->all());

        $this->updateSessionsOnCreatingSeason($season);
        
        return response()->json($season);
    }

    // delete season
    public function deleteSeason(Request $request, $id) {
        $season = Season::findOrFail($id);
        // auth
        if (!$this->authSeason($request, $season->group_id)) {
            return response()->json([
                'error' => 'Not authorized to add season'
            ], 403);
        }

        $this->updateSessionsOnDeletingSeason($season);

        Season::destroy($id);

        return response()->json([
            'message' => 'Season deleted'
        ]);
    }

    // put game sessions in the season that have dates in this season
    private function updateSessionsOnCreatingSeason($season) {
        Session::where('game_id', $season->game_id)->where('group_id', $season->group_id)->where('date', '>=', $season->start_date)->where('date', '<=', $season->end_date)
            ->update(['season_id' => $season->id]);
    }

    // remove season values from game sessions that have dates in this season
    private function updateSessionsOnDeletingSeason($season) {
        Session::where('game_id', $season->game_id)->where('group_id', $season->group_id)->where('date', '>=', $season->start_date)->where('date', '<=', $season->end_date)
            ->update(['season_id' => null]);
    }

    // validate that the season does not overlap with an existing season. returns true if there's an overlap
    private function doesOverlapExist(Request $request, $id = null) {
        $seasons = Season::where('group_id', $request->input('group_id'))->where('game_id', $request->input('game_id'));

        // if season being modified, we don't care if it overlaps with itself
        if (!is_null($seasons)) {
            $seasons = $seasons->where('id', '!=', $id);  
        }
        $seasons = $seasons->get();
        foreach($seasons as $season) {
            if (($request->input('start_date') >= $season->start_date) &&
                ($request->input('start_date') <= $season->end_date) ||
                ($request->input('end_date') >= $season->start_date) &&
                ($request->input('end_date') <= $season->end_date)) {
                return true;
            }
        }

        return false;
    }

    // authorization for site admins or group admins
    private function authSeason(Request $request, $groupid) {
        return $request->user->is_admin ||
            \DB::table('group_user')->where('user_id', $request->user->id)->where('group_id', $groupid)->where('is_group_admin', true)->exists();
    }
}
