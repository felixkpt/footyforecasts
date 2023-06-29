<?php

namespace App\Http\Controllers\Admin\Teams\Team;

use App\Http\Controllers\Controller;
use App\Repositories\TeamRepository;
use App\Services\Common;
use App\Services\Games\Games;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TeamController extends Controller
{
    private $repo;

    public function __construct(TeamRepository $repo)
    {
        $this->repo = $repo;
    }

    function q($id, $i = 0)
    {
        $y = Carbon::now()->subYears($i)->year . '_games';
        try {
            $gameModel = gameModel($y);
        } catch (Exception $e) {
            return false;
        }

        $table = $gameModel->getTable();

        $games = DB::table($table)
            ->join('teams as hteam', $table . '.home_team_id', 'hteam.id', )
            ->join('teams as ateam', $table . '.away_team_id', 'ateam.id', )
            ->where(function ($q) use ($table, $id) {
                $q->where($table . '.home_team_id', $id)->orWhere($table . '.away_team_id', $id);
            })
            ->select(
                $table . '.*',
                'hteam.name as home_team',
                'ateam.name as away_team'
            );

        return $games;
    }
    function show($id)
    {
        $team = $this->repo->findById($id);


        $combinedQuery = DB::query()->fromSub($this->q($id, 0), 'subquery');

        $max_past_years = 4;
        $i = 0;
        while ($combinedQuery->count() < 100 && $i < $max_past_years) {
            $i++;

            $games = $this->q($id, $i);
            if ($games === false)
                break;
            $combinedQuery->when($games, fn($q) => $q->unionAll(DB::query()->fromSub($games, 'subquery')));

        }

        $games = DB::query()->fromSub($combinedQuery, 'result')
            ->orderby('date_time', 'desc')->paginate(5);

        // foreach ($results as $res) {
        //     echo "$res->date_time $res->home_team vs $res->away_team ($res->ht_results) ($res->ft_results)<br>";
        // }
        // dd('');

        return Inertia::render('Teams/Team/Show', compact('team', 'games'));
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
        $team = $this->repo->findById($id);

        $games = new Games();
        $games = $games->getGames($id, true);
        $team = array_merge($team->toArray(), $games);

        return Inertia::render('Teams/Team/Fixtures', compact('team'));
    }

    protected function getFixtures($id)
    {
        $games = new Games();

        return respond(['data' => $games->fixtures($id)]);
    }

    // protected function detailedFixtures($id)
    // {
    //     // Detailed fixture for existing games, so let's get this year's table
    //     $table = Carbon::now()->year . '_games';

    //     $game = gameModel($table);

    //     $all_res = [];
    //     $game
    //         ->where(fn ($q) => $q->where('home_team_id', $id)->orwhere('away_team_id', $id))
    //         ->where('fetching_fixture_state', 0)->chunk(2, function ($games) use (&$all_res, $table) {
    //             $res = [];
    //             foreach ($games as $game) {
    //                 $gamest = new Games();
    //                 $game = $game->toArray();
    //                 $game['table'] = $table;
    //                 $res[] = ['fixture' => '(#' . $game['id'] . ')', 'fetch_details' => ['action' => $gamest->detailedFixture($game)]];
    //             }

    //             $all_res = array_merge($all_res, $res);

    //             return false;
    //         });

    //     $games = $this->getGames($id);
    //     return respond(['data' => ['res' => $all_res, 'games' => $games]]);
    // }

    protected function detailedFixtures($id)
    {
        // Detailed fixture for existing games, so let's get this year's table
        $table = Carbon::now()->year . '_games';

        $game = gameModel($table);

        Common::checkCompetitionAbbreviation($table);

        $games = new Games();

        $team = $this->repo->findById($id);
        $games = $games->getGames($id, true);

        $team = array_merge($team->toArray(), $games);
        return Inertia::render('Teams/Team/DetailedFixtures', compact('team'));
    }

    protected function getDetailedFixtures($id)
    {

        // Detailed fixture for existing games, so let's get this year's table
        $table = Carbon::now()->year . '_games';

        $gameModel = gameModel($table);

        $games = new Games();
        $all_res = $games->detailedFixture($id, $gameModel, false);

        $this->repo->update($id, ['last_detailed_fetch' => Carbon::now()]);

        $team = $this->repo->findById($id);
        $games = $games->getGames($id, true);

        $team = array_merge($team->toArray(), $games);

        return respond(['data' => ['res' => $all_res, 'team' => $team]]);
    }

    protected function results($id)
    {
        dd($id, 'ress');
    }

    protected function changeStatus($id)
    {
        $item = $this->repo->model->find($id);

        $state = $item->status == 1 ? 'Activated' : 'Deactivated';
        $item->update(['status' => !$item->status]);

        $item = $this->repo->findById($id, ['*']);
        return respond(['data' => ['team' => $item, 'status' => $state]]);
    }
}