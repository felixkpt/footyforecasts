<?php

namespace App\Http\Controllers\Admin\Teams;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Inertia\Inertia;

class TeamsController extends Controller
{
    function index()
    {
        return Inertia::render('Teams/Index');
    }

    function show($id)
    {
        $team = Team::whereuuid($id)->first()->toArray();
        return Inertia::render('Teams/Team/Show', compact('team'));
    }

}
