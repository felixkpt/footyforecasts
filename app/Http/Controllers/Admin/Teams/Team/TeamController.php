<?php

namespace App\Http\Controllers\Admin\Teams\Team;

use App\Http\Controllers\Controller;
use App\Repositories\TeamRepository;
use App\Services\Games;
use Inertia\Inertia;

class TeamController extends Controller
{
    private $repo;

    public function __construct(TeamRepository $repo)
    {
        $this->repo = $repo;
    }

    function show($id)
    {
        $team = $this->repo->findById($id);
        return Inertia::render('Teams/Team/Show', compact('team'));
    }

    function checkMatches($id)
    {
        $team = $this->repo->findById($id);
        return Inertia::render('Teams/Team/Actions', compact('team'));
    }

    function checkMatchesAction($id)
    {
        return $this->get_method(request()->status, $id);
    }

    function get_method($action, $id)
    {
        $object = $this;
        return call_user_func_array(array($object, $action), ['id' => $id]);
    }

    protected function fixtures($id)
    {
        $games = new Games();
        return $games->fixtures($id);
    }
    protected function detailedFixtures($id)
    {
        $games = new Games();
        return $games->detailedFixtures($id);
    }

    protected function results($id)
    {
        dd($id, 'ress');
    }

    protected function changeStatus($id)
    {
        dd($id, 'ss', request()->status);
    }

    private function saveMatch($match)
    {
        dd($match);
    }
}
