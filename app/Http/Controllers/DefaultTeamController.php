<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class DefaultTeamController extends Controller
{
    public function __invoke(Request $request, Team $team)
    {
        $user = $request->user();

        if(!$user->ownsTeam($team))
            abort(403, 'Access Denied');

        $user->personalTeam()->update(['personal_team' => false]);

        $team->update(['personal_team' => true]);
    }
}
