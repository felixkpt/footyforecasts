<?php

namespace App\Services\Games;

use App\Repositories\TeamRepository;
use App\Services\Games\Traits\DBActions;
use App\Services\Games\Traits\Source1;
use Carbon\Carbon;

class Games
{

    use DBActions;
    use Source1;
    private $repo;

    protected $existing_competition;

    public function __construct()
    {
        $this->repo = new TeamRepository();
    }

    /**
     * 
     * This function sits in between DBActions and any Source
     * 
     * */
    function detailedFixture($id, $gameModel, $is_competition = false, $ignore_update_status = false, $existing_competition = null)
    {
        $this->existing_competition = $existing_competition;
        $table = $gameModel->getTable();

        $chunk = 3;
        if (isset(request()->limit) && request()->limit < $chunk)
            $chunk = request()->limit;

        $all_res = [];
        $gameModel
            ->whereNotNull($table . '.url')
            ->when($is_competition === true, fn($q) => $q->where('competition_id', $id))
            ->when($is_competition === false, fn($q) => $q->where('home_team_id', $id)->orwhere('away_team_id', $id))
            ->when($ignore_update_status === false, fn($q) => $q->where('update_status', 0))
            ->leftjoin('competitions', $table . '.competition_id', 'competitions.id')
            ->leftjoin('countries', 'competitions.country_id', 'countries.id')
            ->select(
                $table . '.*',
                'competitions.name as competition',
                'countries.name as country',
            )
            ->orderby('updated_at', 'asc')
            ->chunk($chunk, function ($games) use (&$all_res, $table) {
                $res = [];
                foreach ($games as $game) {
                    // Stop chunk processing of limit is supplied and reached
                    if (request()->limit && count($all_res) >= request()->limit)
                        return false;

                    $game = $game->toArray();
                    $game['table'] = $table;
                    $res[] = [
                        'fixture' => '(#' . $game['id'] . ')',
                        'competition' => $game['competition'] ?? null,
                        'country' => $game['country'] ?? null,
                        'teams' => ['home_team_id' => $game['home_team_id'], 'away_team_id' => $game['away_team_id']],
                        'fetch_details' => $this->doDetailedFixture($game)
                    ];
                }

                $all_res = array_merge($all_res, $res);
            });

        // update last_detailed_fetch for this team if detailedFixture is called from a competition
        if ($is_competition === true)
            $this->updateTeamsLastDetailedFetch(array_column($all_res, 'teams'));

        return $all_res;
    }

    /**
     * 
     * This function sits in between DBActions and any Source
     * 
     * */
    function detailedFixture2($games, $gameModel, $is_competition = false, $ignore_update_status = false, $existing_competition = null)
    {
        $this->existing_competition = $existing_competition;
        $table = $gameModel->getTable();

        $chunk = 3;
        if (isset(request()->limit) && request()->limit < $chunk)
            $chunk = request()->limit;

        $all_res = [];
        $gameModel
            ->whereNotNull($table . '.url')
            ->when($is_competition === true, fn($q) => $q->where('competition_id', $id))
            ->when($is_competition === false, fn($q) => $q->where('home_team_id', $id)->orwhere('away_team_id', $id))
            ->when($ignore_update_status === false, fn($q) => $q->where('update_status', 0))
            ->leftjoin('competitions', $table . '.competition_id', 'competitions.id')
            ->leftjoin('countries', 'competitions.country_id', 'countries.id')
            ->select(
                $table . '.*',
                'competitions.name as competition',
                'countries.name as country',
            )
            ->orderby('updated_at', 'asc')
            ->chunk($chunk, function ($games) use (&$all_res, $table) {
                $res = [];
                foreach ($games as $game) {
                    // Stop chunk processing of limit is supplied and reached
                    if (request()->limit && count($all_res) >= request()->limit)
                        return false;

                    $game = $game->toArray();
                    $game['table'] = $table;
                    $res[] = [
                        'fixture' => '(#' . $game['id'] . ')',
                        'competition' => $game['competition'] ?? null,
                        'country' => $game['country'] ?? null,
                        'teams' => ['home_team_id' => $game['home_team_id'], 'away_team_id' => $game['away_team_id']],
                        'fetch_details' => $this->doDetailedFixture($game)
                    ];
                }

                $all_res = array_merge($all_res, $res);
            });

        // update last_detailed_fetch for this team if detailedFixture is called from a competition
        if ($is_competition === true)
            $this->updateTeamsLastDetailedFetch(array_column($all_res, 'teams'));

        return $all_res;
    }

    function updateTeamsLastDetailedFetch($teams)
    {
        // we need to remove duplicate team IDs so that when we update last_detailed_fetch we may not repeat ourselves
        $team_ids = array_reduce(array_column($teams, 'teams'), function ($res, $curr) {

            $h_id = $curr['home_team_id'];
            $a_id = $curr['away_team_id'];
            if (!in_array($h_id, $res))
                array_push($res, $h_id);

            if (!in_array($a_id, $res))
                array_push($res, $a_id);

            return $res;
        }, []);

        // we then loop thru each team id to see if the team still really has no any untouched games
        foreach ($team_ids as $team_id)
            $this->updateTeamLastDetailedFetch($team_id);
    }

    /**
     * updateTeamLastDetailedFetch
     * @param mixed $team_id
     * @return void
     */
    function updateTeamLastDetailedFetch($team_id)
    {
        $this->repo->model->find($team_id)->update(['last_detailed_fetch' => Carbon::now()]);
    }
}