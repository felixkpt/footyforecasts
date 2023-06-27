<?php

namespace App\Http\Controllers\Admin\Competitions\Competition;

use App\Http\Controllers\Controller;
use App\Repositories\CompetitionRepository;
use App\Repositories\TeamRepository;
use App\Services\Common;
use App\Services\Games\Games;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class CompetitionController extends Controller
{
    private $repo;

    public function __construct(CompetitionRepository $repo)
    {
        $this->repo = $repo;
    }
    function show($id)
    {
        $competition = $this->repo->findById($id, ['*'], ['teams'])->toArray();
        return Inertia::render('Competitions/Competition/Show', compact('competition'));
    }

    function store($id)
    {
        request()->validate([
            'source' => 'required'
        ]);

        $is_domestic = request()->is_domestic;

        $competition = $this->repo->findById($id, ['*'], ['country']);

        return Common::updateCompetitionAndHandleTeams($competition, $competition->country, $is_domestic, null);
    }

    function checkMatches($id)
    {
        $competition = $this->repo->findById($id);
        return Inertia::render('Competitions/Competition/Actions', compact('competition'));
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

    protected function predictions($id)
    {
        $competition = $this->repo->findById($id);
        return Inertia::render('Competitions/Competition/Predictions', compact('competition'));
    }

    protected function fixtures($id)
    {
        $competition = $this->getCompetition($id);

        return Inertia::render('Competitions/Competition/Fixtures', compact('competition'));
    }

    protected function getFixtures($id)
    {

        $testdate = Carbon::now()->subDays(2)->toDateTimeString();

        $chunk = 3;
        if (isset(request()->limit) && request()->limit < $chunk) $chunk = request()->limit;

        $all_res = [];
        $repo = new TeamRepository();
        $repo->model->where('competition_id', $id)->where(function ($q) use ($testdate) {
            $q->where('last_fetch', '<=', $testdate)->orWhereNull('last_fetch');
        })->orderby('last_fetch', 'asc')
            ->chunk($chunk, function ($teams) use (&$all_res) {

                // Stop chunk processing of limit is supplied and reached
                if (request()->limit && count($all_res) >= request()->limit) return false;

                $res = [];
                foreach ($teams as $team) {
                    $games = new Games();
                    $res[] = ['team' => $team->name . ' (#' . $team->id . ')', 'fetch_details' => $games->fixtures($team->id, false)];
                }

                $all_res = array_merge($all_res, $res);
            });

        $competition = $this->getCompetition($id);

        return respond(['data' => ['res' => $all_res, 'competition' => $competition]]);
    }

    function getCompetition($id)
    {
        $testdate = Carbon::now()->subDays(1)->toDateTimeString();

        $competition = $this->repo->findById($id, ['*'], ['teams' => function ($q) use ($testdate) {
            $q->where('last_fetch', '<=', $testdate)->orWhereNull('last_fetch');
        }]);

        $recently_fetched_teams = $this->repo->findById($id, ['*'], ['teams' => function ($q) use ($testdate) {
            $q->where('last_fetch', '>', $testdate)->orderby('last_fetch', 'desc');
        }])->teams;

        $competition = array_merge($competition->toArray(), ['recentlyFetchedTeams' => $recently_fetched_teams->toArray()]);
        return $competition;
    }

    protected function detailedFixtures($id)
    {
        // Detailed fixture for existing games, so let's get this year's table
        $table = Carbon::now()->year . '_games';

        $game = gameModel($table);

        Common::checkCompetitionAbbreviation($table);

        $competition = $this->getGames($id);

        return Inertia::render('Competitions/Competition/DetailedFixtures', compact('competition'));
    }

    protected function getDetailedFixtures($id)
    {

        // Detailed fixture for existing games, so let's get this year's table
        $table = Carbon::now()->year . '_games';

        $game = gameModel($table);

        $games = new Games();
        $all_res = $games->detailedFixture($id, $game, true);

        $this->repo->update($id, ['last_detailed_fetch' => Carbon::now()]);

        $competition = $this->getGames($id);
        return respond(['data' => ['res' => $all_res, 'competition' => $competition]]);
    }

    function getGames($id)
    {
        // Detailed fixture for existing games, so let's get this year's table
        $table = Carbon::now()->year . '_games';

        $game = gameModel($table);

        $competition = $this->repo->findById($id, ['*']);

        $games = $game->where('competition_id', $id)->where('fetching_fixture_state', 0)->get();
        $recently_fetched_games = $game->where('competition_id', $id)->where('fetching_fixture_state', '>', 0)->get();

        $competition = array_merge($competition->toArray(), ['games' => $games->toArray(), 'recentlyFetchedGames' => $recently_fetched_games->toArray()]);

        return $competition;
    }

    protected function update($id)
    {
        $competition = $this->repo->findById($id);
        return Inertia::render('Competitions/Competition/Update', compact('competition'));
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

        $item = $this->repo->findById($id, ['*'], ['teams']);
        return respond(['data' => ['competition' => $item, 'status' => $state]]);
    }
}
