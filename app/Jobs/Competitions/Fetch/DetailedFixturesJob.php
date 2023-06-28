<?php

namespace App\Jobs\Competitions\Fetch;

use App\Mail\Competitions\Fetch\FixturesFinishedMail;
use App\Mail\Competitions\Fetch\FixturesMail;
use App\Repositories\CompetitionRepository;
use App\Repositories\TeamRepository;
use App\Services\Games\Games;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DetailedFixturesJob implements ShouldQueue, FixturesInterface
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, FetchTrait;

    // Properties
    // in mins
    protected $script_max_duration = 5;
    protected $last_fetch_minutes = 1;
    protected $last_fetch_minutes_teams = 20;
    protected $start;
    protected $stop;
    private $repo;
    private $teamRepo;
    private $games_info = [];
    protected $mailUpdates = [];
    protected $last_fetch_col = 'last_detailed_fetch';
    protected $chunk = 3;
    protected $limit = 10;
    protected $chunk_games = 5;
    protected $limit_games = 50;
    protected $games_table;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->start = Carbon::now()->toDateTimeString();
        $this->stop = Carbon::now()->addMinutes($this->script_max_duration)->toDateTimeString();

        $this->repo = new CompetitionRepository();
        $this->teamRepo = new TeamRepository();


        $i = 0;
        while (True) {
            $y = Carbon::now()->subYears(rand(abs($i), abs($i)))->year . '_games';
            try {
                $gameModel = gameModel($y);
            } catch (Exception $e) {
                break;
            }

            if ($gameModel->whereNotNull('url')->where('update_status', 0)->count() > 0) {
                $this->games_table = $y;
                break;
            }
            $i--;
        }

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $duration = 0;
        if ($this->games_table)
            $duration = $this->commonHandle();

        $message = count($this->mailUpdates) . " detailed competitions fixtures finished in $duration.\n";
        echo $message;

        $arr = [
            'is_detailed' => true,
            'name' => 'Felix',
            'message' => $message,
            'table' => $this->games_table,
            'games_info' => $this->games_info
        ];

        try {
            Mail::to('kiptookipkiro@gmail.com')->send((new FixturesFinishedMail($arr)));
        } catch (Exception $e) {
            Log::info('DetaileFixtures finished:', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Updates competitions.
     *
     * @param  mixed  $competitions
     * @return void
     * 
     */
    function update($competitions)
    {
        // Detailed fixture for existing games, so let's get this year's table
        try {
            $gameModel = gameModel($this->games_table);
        } catch (Exception $e) {
            Log::critical($e->getMessage());
            return;
        }

        $games = new Games();
        $mailUpdates = [];

        foreach ($competitions as $competition) {

            if ($this->competitionHasTeams($competition->id) === true) {
                $res = $this->competitionFixtures($competition, $gameModel);
                $data = ['competition' => $competition->name . ' ( Domestic)', 'teams' => $res];
            } else {
                $res = $games->detailedFixture($competition->id, $gameModel, false, false);
                $data = ['competition' => $competition->name . ' ( Non domestic)', 'teams' => $res];
            }

            if (count($data) === 0)
                continue;

            array_push($mailUpdates, $data);
            array_push($this->mailUpdates, $data);

            $this->repo->update($competition->id, [$this->last_fetch_col => Carbon::now()]);
        }

        $now = Carbon::now();
        $testdate = preg_replace('# after$#', '', $now->diffForHumans($this->start, null, false, 2)) . '';

        $message = "Running $this->chunk per chunk, detailed competitions fixtures finished in $testdate\n";
        echo $message;

        $games_info = ['competitions' => $competitions->count(), 'all_games' => $gameModel->count(), 'untouched_games' => $gameModel->where('update_status', 0)->count()];
        $this->games_info[] = $games_info;

        $arr = [
            'is_detailed' => true,
            'name' => 'Felix',
            'chunk' => $this->chunk,
            'message' => $message,
            'competitions_and_teams' => $mailUpdates,
            'table' => $this->games_table,
            'games_info' => $games_info
        ];

        try {
            Mail::to('kiptookipkiro@gmail.com')->send((new FixturesMail($arr)));
        } catch (Exception $e) {
            Log::info('DetaileFixtures:', ['error' => $e->getMessage()]);
        }
    }

    function competitionHasTeams($id)
    {
        return !!$this->teamRepo->model->where('competition_id', $id)->first();
    }

    /**
     * Fetches competition teams and their detailed fixture information.
     *
     * @param  $competition The competition object.
     * @param  $gameModel The game model object.
     * @return array An array containing the fetched teams and their detailed fixture information.
     */
    public function competitionFixtures($competition, $gameModel = null)
    {

        $table = $gameModel->getTable();
        // Set the limit for the request to fetch teams
        request()->merge(['limit' => $this->limit_games]);

        // Calculate the test date based on the last fetch hours for teams
        $testdate = Carbon::now()->subMinutes($this->last_fetch_minutes_teams)->toDateTimeString();

        // Set the chunk size for processing teams
        $chunk = $this->chunk_games;
        if (isset(request()->limit) && request()->limit < $chunk) {
            $chunk = request()->limit;
        }
        // Initialize an empty array to store the fetched teams and their fixture details
        $all_res = [];

        $gameModel
            ->join('teams as hteam', $table . '.home_team_id', 'hteam.id', )
            ->join('teams as ateam', $table . '.away_team_id', 'ateam.id', )
            ->where('hteam.competition_id', $competition->id)->where(function ($q) use ($testdate) {
                $q->where('hteam.' . $this->last_fetch_col, '<=', $testdate)->orWhereNull('hteam.' . $this->last_fetch_col);
            })
            ->whereNotNull($table . '.url')
            ->where($table . '.update_status', 0)
            // ->where($table . '.id', '01h3w5xz58qc48nw14m6pr9ar7')
            ->orderby('hteam.' . $this->last_fetch_col, 'asc')
            ->select(
                $table . '.*',
                'hteam.name as home_team',
                'ateam.name as away_team'
            )
            ->chunk($chunk, function ($games) use (&$all_res, $table, $competition) {

                $res = [];
                foreach ($games as $game) {
                    // Stop chunk processing of limit is supplied and reached
                    if (request()->limit && count($all_res) >= request()->limit)
                        return false;

                    if ($this->withinTimeLimit($competition) === false)
                        return false;

                    $game = $game->toArray();
                    $game['table'] = $table;
                    $res[] = [
                        'fixture' => '(#' . $game['id'] . ')',
                        'competition' => $game['competition'] ?? null,
                        'country' => $game['country'] ?? null,
                        'teams' => ['home_team_id' => $game['home_team_id'], 'away_team_id' => $game['away_team_id']],
                        'fetch_details' => (new Games())->doDetailedFixture($game, $competition)
                    ];
                }

                $all_res = array_merge($all_res, $res);

                (new Games())->updateTeamsLastDetailedFetch(array_column($all_res, 'teams'));

            });


        return $all_res;
    }

    function handleTeamFixtures($all_res, $teams, $gameModel = null, $competition = null)
    {
    }
}