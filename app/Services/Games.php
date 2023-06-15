<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Odd;
use App\Models\Stadium;
use App\Repositories\EloquentRepository;
use App\Repositories\TeamRepository;
use Illuminate\Support\Carbon;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use LaracraftTech\LaravelDynamicModel\DynamicModelFactory;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class Games
{

    private $repo;
    private $game;

    public function __construct()
    {
        $this->repo = new TeamRepository();
    }

    /**
     * Get team's fixtures
     * @param int|string $team_id
     * @return mixed
     */
    function fixtures($team_id)
    {

        $team = $this->repo->findById($team_id);

        $source = env('SOURCE_SITE') . $team->url;

        $browser = new HttpBrowser(HttpClient::create());

        $browser->request('GET', $source);

        $html = $browser->getResponse()->getContent();


        $crawler = new Crawler($html);

        $source_team = $crawler->filter('div.moduletable>div.mptlt')->text();
        if (!preg_match('#' . $team->name . '#i', $source_team)) throw new Exception('Error: team mis-match!');

        $games = $crawler->filter('div.moduletable>div.st_scrblock .st_rmain .st_row');

        return $games->each(function (Crawler $node) {

            $date_time = $node->filter('.st_date');
            $date_month = preg_replace('#\/#', '-', $node->filter('.st_date>div:first-child')->text());
            $year = $node->filter('.st_date>div:last-child')->text();
            $date_time = Carbon::parse($date_month . '-' . $year)->format('Y-m-d');

            $hteam = $node->filter('.st_hteam a');
            $home_team_url = $hteam->attr('href');
            $home_team = $hteam->text();

            $fixture = $node->filter('a.stat_link');
            $url = $fixture->attr('href');

            $results = $node->filter('.st_rescnt');
            $ft_results = $results->filter('.st_res')->text();
            $ht_results = $results->filter('.st_htscr')->text();
            $ht_results = preg_replace('#\)|\(#', '', $ht_results);

            $ateam = $node->filter('.st_ateam a');
            $away_team_url = $ateam->attr('href');
            $away_team = $ateam->text();

            return $this->saveGame($date_time, $home_team_url, $home_team, $ft_results, $ht_results, $away_team_url, $away_team, $url);
        });
    }



    private function saveGame(mixed ...$args)
    {
        [$date_time, $home_team_url, $home_team, $ft_results, $ht_results, $away_team_url, $away_team, $url] = $args;

        $table = Carbon::parse($date_time)->format('Y') . '_games';
        $this->createTable($table);

        $date = Carbon::parse($date_time)->format('Y-m-d');
        $time = Carbon::parse($date_time)->format('H:i:s');

        $home_team_init = $home_team;
        $away_team_init = $away_team;

        $home_team = $this->repo->model->where('url', $home_team_url)->first();
        $away_team = $this->repo->model->where('url', $away_team_url)->first();

        if ($home_team && $away_team) {

            $games = $this->gameModel($table);

            $exists = $games->where([['home_team_id', $home_team->id], ['away_team_id', $away_team->id]])->where('date', $date)->first();

            // common columns during create and update
            $arr = [
                'date_time' => $date_time,
                'date' => $date,
                'time' => $time,
                'ht_results' => $ht_results,
                'ft_results' => $ft_results,
            ];

            if (!$exists) {

                $arr = array_merge($arr, [
                    'home_team_id' => $home_team->id,
                    'away_team_id' => $away_team->id,
                    'url' => $url,
                ]);

                $games->create($arr);

                return 'Game saved.';
            } else {
                $exists->update($arr);

                return 'Game updated.';
            }
        } elseif ($home_team) {
            return 'Away team not found, (' . $away_team_init . ').';
        } elseif ($away_team) {
            return 'Home team not found, (' . $home_team_init . ').';
        } else {
            return 'Both teams not found, (' . $home_team_init . ' & ' . $away_team_init . ').';
        }
    }

    /**
     * Get team's detailed fixtures
     * @param int|string $team_id
     * @return mixed
     */
    function detailedFixtures($team_id)
    {

        // Detailed fixture for existing games, so let's get this year's table
        $table = Carbon::now()->year . '_games';

        $game = $this->gameModel($table);

        $games = $game->where('fetching_fixture_state', 0)->where(fn ($q) => $q->where('home_team_id', $team_id)->orwhere('away_team_id', $team_id))->take(10)->get()->toArray();

        if (count($games) === 0) return response(['type' => 'fetching_fixture_state:0_passed', 'data' => 'All saved fixtures are upto date.']);

        $res = array_map(
            function ($game) use ($table) {
                $game['table'] = $table;
                $this->game = $game;
                return $this->detailedFixture();
            },
            $games,
            []
        );

        return $res;
    }

    function detailedFixture()
    {

        $source = env('SOURCE_SITE') . $this->game['url'];

        $browser = new HttpBrowser(HttpClient::create());

        $browser->request('GET', $source);

        $html = $browser->getResponse()->getContent();

        $crawler = new Crawler($html);

        $header = $crawler->filter('div.predictioncontain');
        $home_team_logo = $header->filter('div.lLogo a img.matchTLogo')->attr('src');
        $date_time = $header->filter('time div.date_bah')->text();
        $d = explode(' ', $date_time, 2);
        $date = Carbon::parse(preg_replace('#\/#', '-', $d[0]))->format('Y-m-d');
        $date_time = Carbon::parse($date . ' ' . $d[1])->format('Y-m-d H:i');

        $stadium = $header->filter('div.weather_main_pr span')->text();

        $competition = $crawler->filter('center.leagpredlnk a');
        $competition_url = $competition->attr('href');
        $competition = $competition->text();

        $away_team_logo = $header->filter('div.rLogo a img.matchTLogo')->attr('src');

        $ft_results = $ht_results = null;

        $markets = $crawler->filter('div.rcnt.tr_0');

        $available_markets = ['1' => 'one_x_two', 'X' => 'one_x_two', '2' => 'one_x_two', 'Yes' => 'gg_ng', 'No' => 'gg_ng', 'Over' => 'over_under', 'Under' => 'over_under'];
        $one_x_two = [];
        $gg_ng = [];
        $over_under = [];

        $markets->each(function (Crawler $node) use (
            $available_markets,
            &$one_x_two,
            &$gg_ng,
            &$over_under,
            &$ft_results,
            &$ht_results,
        ) {

            $check = $node->filter('.predict_no .forepr');
            if ($check->count() > 0) {

                $check = $check->text();

                if (isset($available_markets[$check])) {
                    $node->filter('.prmod .haodd span')->each(function (Crawler $node) use (
                        $available_markets,
                        &$one_x_two,
                        &$gg_ng,
                        &$over_under,
                        $check
                    ) {
                        $odds = $node->text();
                        if ($odds > 0 && $odds < 20) {
                            ${$available_markets[$check]}[] = $odds;
                        }
                    });

                    $res = $node->filter('.lscr_td');
                    if ($res->count() > 0) {
                        $ft_results = $res->filter('.l_scr')->text();
                        $ht_results = $res->filter('.ht_scr')->text();
                        $ht_results = preg_replace('#\)|\(#', '', $ht_results);
                    }
                }
            }
        });


        return $this->updateGame($home_team_logo, $date_time, $stadium, $competition, $competition_url, $away_team_logo, $ft_results, $ht_results, $one_x_two, $over_under, $gg_ng);
    }

    private function updateGame(mixed ...$args)
    {
        [$home_team_logo, $date_time, $stadium, $competition, $competition_url, $away_team_logo, $ft_results, $ht_results, $one_x_two, $over_under, $gg_ng] = $args;

        $this->saveTeamLogo($this->game['home_team_id'], $home_team_logo);
        $this->saveTeamLogo($this->game['away_team_id'], $away_team_logo);
        $stadium_id = $this->saveStadium($stadium);
        $competition_id = $this->saveCompetition($competition, $competition_url,);

        $table = Carbon::parse($date_time)->format('Y') . '_games';
        $this->createTable($table);

        $date = Carbon::parse($date_time)->format('Y-m-d');
        $time = Carbon::parse($date_time)->format('H:i:s');

        $games = $this->gameModel($table);

        $exists = $games->find($this->game['id']);

        if ($exists) {
            // common columns during create and update
            $arr = [
                'date_time' => $date_time,
                'date' => $date,
                'time' => $time,
                'ht_results' => $ht_results,
                'ft_results' => $ft_results,
                'competition_id' => $competition_id,
                'stadium_id' => $stadium_id,
                'fetching_fixture_state' => 1
            ];
            $exists->update($arr);

            $this->saveOdds($one_x_two, $over_under, $gg_ng);

            return 'Fixture updated';
        } else {
            // delete fixture, date changed
            DB::table($this->game['table'])->where('id', $this->game['id'])->delete();

            return 'Fixture deleted';
        }
    }

    private function createTable($table)
    {
        if (!Schema::hasTable($table)) {
            Schema::create($table, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->dateTime('date_time');
                $table->date('date');
                $table->time('time')->nullable();
                $table->uuid('home_team_id');
                $table->uuid('away_team_id');
                $table->string('ht_results')->nullable();
                $table->string('ft_results')->nullable();
                $table->uuid('competition_id')->nullable();
                $table->string('url');
                $table->uuid('stadium_id')->nullable();
                $table->uuid('user_id');
                $table->string('status')->default(1);
                $table->tinyInteger('fetching_fixture_state')->default(0);
                $table->timestamps();
            });
        }
    }

    private function gameModel($table)
    {
        return app(DynamicModelFactory::class)->create(Game::class, $table);
    }

    function saveTeamLogo($team_id, $source)
    {
        $exists = $this->repo->findById($team_id);
        if ($exists->img) return true;

        $ext = pathinfo($source, PATHINFO_EXTENSION);
        $filename = "t" . $team_id . '.' . $ext;

        $dest = ("images/teams"); /* Path */
        File::ensureDirectoryExists(storage_path("app/public/" . $dest));

        $dest .= '/' . $filename; /* Complete file name */

        /* Copy the file */
        copy($source, storage_path("app/public/" . $dest));

        $this->repo->update($team_id, ['img' => $dest]);

        return true;
    }
    function saveStadium($name)
    {
        $repo = new EloquentRepository(Stadium::class);

        $res = $repo->updateOrCreate(['name' => $name], [
            'name' => $name
        ]);

        return $res->id;
    }

    function saveOdds(...$args)
    {

        [$one_x_two, $over_under, $gg_ng] = $args;

        if (count($one_x_two) !== 3) return false;

        $repo = new EloquentRepository(Odd::class);

        $res = $repo->updateOrCreate(['game_id' => $this->game['id']], [
            'game_id' => $this->game['id'],
            'home_win_odds' => $one_x_two[0],
            'draw_odds' => $one_x_two[1],
            'away_win_odds' => $one_x_two[2],
            'over_odds' => $over_under[0] ?? null,
            'under_odds' => $over_under[1] ?? null,
            'gg_odds' => $gg_ng[0] ?? null,
            'ng_odds' => $gg_ng[1] ?? null,

        ]);
    }

    function saveCompetition($source, $name)
    {

        return null;

        $ext = pathinfo($source, PATHINFO_EXTENSION);
        $filename = "c" . $this->competition->id . '.' . $ext;

        $dest = "images/competitions/" . Str::slug($this->country->name); /* Path */
        if (!file_exists($dest)) {
            mkdir($dest, 0777, true); /* Create directory */
        }
        $dest .= '/' . $filename; /* Complete file name */

        /* Copy the file */
        copy($source, $dest);

        return $dest;
    }
}
