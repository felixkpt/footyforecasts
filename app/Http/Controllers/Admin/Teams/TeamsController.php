<?php

namespace App\Http\Controllers\Admin\Teams;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Repositories\TeamRepository;
use Inertia\Inertia;

class TeamsController extends Controller
{
    private $repo;

    public function __construct(TeamRepository $repo)
    {
        $this->repo = $repo;
    }

    function index()
    {
        return Inertia::render('Teams/Index');
    }

}
