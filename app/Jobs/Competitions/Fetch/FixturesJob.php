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

class FixturesJob implements ShouldQueue, FixturesInterface
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, FetchTrait;

    // Properties
    // in mins
    protected $script_max_duration = 5;
    protected $start;
    protected $stop;
    protected $last_fetch_minutes = 5;
    protected $last_fetch_minutes_teams = 10;
    private $repo;
    private $teamRepo;
    protected $mailUpdates = [];
    protected $last_fetch_col = 'last_fetch';
    protected $chunk = 3;
    protected $limit = 15;
    protected $chunk_teams = 3;
    protected $limit_teams = 10;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->start = Carbon::now()->toDateTimeString();
        $this->stop = Carbon::now()->addMinutes($this->script_max_duration)->toDateTimeString();

        $this->repo = new CompetitionRepository();
        $this->teamRepo = new TeamRepository();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Executes the commonHandle method and retrieves the duration
        $duration = $this->commonHandle();

        $message = count($this->mailUpdates) . " competitions fixtures updates finished in $duration.\n";
        echo $message;

        try {
            // Sends email with the FixturesFinishedMail
            Mail::to('kiptookipkiro@gmail.com')->send((new FixturesFinishedMail(['is_detailed' => false, 'name' => 'Felix', 'message' => $message])));
        } catch (Exception $e) {
            Log::info('Fixtures:', ['error' => $e->getMessage()]);
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
        $mailUpdates = [];
        foreach ($competitions as $competition) {
            $res = $this->competitionTeams($competition, null);
            $data = ['competition' => $competition->name, 'teams' => $res];

            if (count($data) === 0) continue;

            array_push($mailUpdates, $data);
            array_push($this->mailUpdates, $data);

            $competition->update([$this->last_fetch_col => Carbon::now()]);
        }

        $now = Carbon::now();
        $testdate = preg_replace('# after$#', '', $now->diffForHumans($this->start, null, false, 2)) . '';

        $message = "Running $this->chunk per chunk updates finished in $testdate\n";
        echo $message;

        try {
            // Sends email with the FixturesMail
            Mail::to('kiptookipkiro@gmail.com')->send((new FixturesMail(['is_detailed' => false, 'name' => 'Felix', 'message' => $message, 'competitions_and_teams' => $mailUpdates, 'chunk' => $this->chunk])));
        } catch (Exception $e) {
            Log::info('Fixtures finished:', ['error' => $e->getMessage()]);
        }
    }

     /**
     * Fetches competition teams and their detailed fixture information.
     *
     * @param  $competition The competition object.
     * @param  $gameModel The game model object.
     * @return array An array containing the fetched teams and their detailed fixture information.
     */
    public function competitionTeams($competition, $gameModel = null)
    {
        // Set the limit for the request to fetch teams
        request()->merge(['limit' => $this->limit_teams]);

        // Calculate the test date based on the last fetch hours for teams
        $testdate = Carbon::now()->subMinutes($this->last_fetch_minutes_teams)->toDateTimeString();

        // Set the chunk size for processing teams
        $chunk = $this->chunk_teams;
        if (isset(request()->limit) && request()->limit < $chunk) {
            $chunk = request()->limit;
        }

        // Initialize an empty array to store the fetched teams and their fixture details
        $all_res = [];
        // Query the team model to fetch teams for the given competition
        $this->teamRepo->model->where('competition_id', $competition->id)->where(function ($q) use ($testdate) {
            $q->where($this->last_fetch_col, '<=', $testdate)->orWhereNull($this->last_fetch_col);
        })
            ->orderby($this->last_fetch_col, 'asc')
            ->chunk($chunk, function ($teams) use (&$all_res, $gameModel, $competition) {

                // Stop chunk processing if the limit is supplied and reached
                if (request()->limit && count($all_res) >= request()->limit) return false;

                if ($this->withinTimeLimit($competition) === false) return false;

                $all_res = $this->handleTeamFixtures($all_res, $teams, $gameModel, $competition);
            });

        return $all_res;
    }
    
    function handleTeamFixtures($all_res, $teams, $gameModel = null, $competition = null)
    {
        $games = new Games();
        foreach ($teams as $team) {
            $games = new Games();
            $res = [
                'team' => $team->name . ' (#' . $team->id . ')',
                'fetch_details' => $games->fixtures($team->id, false)
            ];
            $all_res = array_merge($all_res, $res);

            $this->teamRepo->update($team->id, [$this->last_fetch_col => Carbon::now()]);
        }

        return array_merge($all_res, $res);
    }
}
