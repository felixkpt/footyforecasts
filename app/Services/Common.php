<?php

namespace App\Services;

use App\Models\CompetitionAbbreviation;
use App\Models\Country;
use App\Models\Odd;
use App\Models\Stadium;
use App\Repositories\CompetitionRepository;
use App\Repositories\EloquentRepository;
use App\Repositories\TeamRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Illuminate\Support\Facades\Storage;

class Common
{
    public static function TeamRepo()
    {
        return new TeamRepository();
    }
    public static function CompetitionRepo()
    {
        return new CompetitionRepository();
    }

    /**
     * Update competition and handle associated teams.
     *
     * @param \App\Models\Competition $competition The competition object to update.
     * @param string $country The country associated with the competition.
     * @param bool $isDomestic Indicates whether the competition is domestic or not.
     * @param mixed|null $crawler (optional) The crawler object used for web scraping. If not provided, it will be created internally.
     * @param mixed|null $responseType (optional) The response type for the API response. If not provided, the default will be used.
     * @param bool $ignoreLastFetch (optional) Whether to ignore the last fetch date and force updating. Defaults to false.
     * 
     * @return mixed The API response with the updated competition and associated teams.
     */
    static function updateCompetitionAndHandleTeams($competition, $country, $is_domestic, $crawler = null, $response_type = null, $ignore_last_fetch = false)
    {

        $last_fetch = Carbon::createFromDate($competition->last_fetch);
        $now = Carbon::now();
        $testdate = $last_fetch->diffInDays($now);

        if ($ignore_last_fetch === false && $competition->last_fetch !== null && $testdate < 1)
            return respond(['data' => ['type' => 'success', 'message' => 'Last fetch is less than 1 day.']], 200, $response_type);

        if (!$crawler) {
            $html = Client::request(Common::resolve($competition->url));

            $crawler = new Crawler($html);
        }

        $teams = $crawler->filter('table.standings')->first()->filter('tr:not(.heading)')->each(function (Crawler $node) {

            $row = $node->filter('td')->each(function (Crawler $node, $i) {
                $arr = ['position', 'team', 'points', 'games_played', 'won', 'draw', 'lost', 'goals_for', 'goals_for', 'goal_difference'];

                $data = $node->text();
                if ($i === 1)
                    $data = ['name' => $node->text(), 'url' => $node->filter('a')->attr('href')];

                return [$arr[$i] => $data];
            });

            return $row;
        });

        $added = Common::saveTeams($teams, $competition, $country, $is_domestic);

        $removed = self::teamRepo()->model->where('competition_id', $competition->id)->whereNotIn('id', array_column($added, 'id'));
        $removedTeams = $removed->get(['id', 'name']);
        // Removing teams not currently listed under this competition on the source, but are on the DB
        if ($removed->count() > 0)
            $removed->update(['competition_id' => null]);

        if ($competition->action === 'updated')
            self::competitionRepo()->update($competition->id, ['last_fetch' => Carbon::now()]);

        return respond(['data' => ['competition' => $competition->toArray(), 'teams' => $added, 'removedTeams' => $removedTeams]], 200, $response_type);
    }

    static function saveCompetition(string|array $source, string $name = null, $is_domestic = null)
    {

        $img = null;
        if (is_array($source)) {
            ['src' => $source, 'name' => $name, 'img' => $img] = $source;
        }

        $source = ltrim($source, '/');
        if (!Str::startsWith($source, 'en/'))
            $source = 'en/' . $source;

        $source = ltrim($source, '/');
        if (!Str::startsWith($source, 'en/'))
            $source = 'en/' . $source;

        $country = self::saveCountry($source);

        if (!$country)
            return;

        $source = preg_replace('#/+#', '/', '/' . $source);

        $competition = self::competitionRepo()->model->where('country_id', $country->id)->where('url', $source)->first();
        if ($competition) {
            $competition->action = 'updated';
            return $competition;
        }
        $name_init = $name;

        if ($img === null) {
            $html = Client::request(self::resolve($source));
            $crawler = new Crawler($html);

            $header = $crawler->filter('.contentmiddle h1.frontH');

            $img = $header->filter('img')->attr('src');
            $name = $header->filter('span')->text();
        }

        if (!preg_match('#' . $name_init . '#i', $name))
            return false;

        $name = preg_replace('#Free football predictions for ' . $country->name . ' #i', '', $name);
        $name = preg_replace('#Free football predictions for#i', '', $name);

        $name = preg_replace('#Standings ' . $country->name . ' #i', '', $name);
        $name = preg_replace('#Standings#i', '', $name);

        $name = trim(preg_replace('# table$#i', '', $name));

        $arr = [
            'name' => $name,
            'slug' => Str::slug($name),
            'country_id' => $country->id,
            'url' => $source,
            'status' => 1,
            'is_domestic' => $is_domestic
        ];

        $suff = '/standing';
        if (!Str::endsWith($source, $suff) && Client::status(Common::resolve($source . $suff)) === 200) {
            $source = $source . $suff;
            array_push($arr, ['is_domestic' => true]);
        }

        $competition = tap(self::competitionRepo()->model->updateOrCreate(
            [
                'name' => $name,
                'url' => $source,
                'country_id' => $country->id,
            ],
            $arr
        ), function ($competition) use ($img, $country) {
            $competition->action = $competition->wasRecentlyCreated === true ? 'created' : 'updated';

            $res = self::saveCompetitionLogo($img, $country, $competition);
            if ($res) {
                self::competitionRepo()->update($competition->id, ['img' => $res]);
                $competition->img = $res; // Update the 'img' attribute of the competition
            }
        });

        return $competition;
    }


    static function saveCompetitionLogo($source, $country, $competition)
    {
        // Log::info('savecompelogo::', [$source, $country->toArray()]);

        $ext = pathinfo($source, PATHINFO_EXTENSION);
        $filename = "c" . $competition->id . '.' . $ext;

        $dest = "public/images/competitions/" . $country->slug . '/' . $filename; /* Complete path & file name */

        if (Client::downloadFileFromUrl($source, $dest))
            return $dest;
        else
            return null;
    }

    static function resolve($source)
    {
        return preg_replace("#(?<!:)/+#im", "/", env('SOURCE_SITE') . '/' . $source);
    }

    static function saveCountry($source)
    {
        $slug = self::getCountrySlug($source);
        $repo = new EloquentRepository(Country::class);
        $country = $repo->model->where('slug', $slug)->first();


        if (!$country && $slug) {
            Log::info('Country not found:', ['slug' => $slug]);
            // $country = $repo->create([
            //     'name' => Str::title($slug),
            //     'slug' => $slug,
            // ]);
        }

        if ($country && $country->has_competitions == 0)
            $repo->model->find($country->id)->update(['has_competitions' => true]);

        return $country;
    }

    static function saveStadium($name)
    {
        $repo = new EloquentRepository(Stadium::class);

        $res = $repo->updateOrCreate(['name' => $name], [
            'name' => $name
        ]);

        return $res;
    }

    static function saveOdds($data)
    {

        if (count($data['one_x_two']) !== 3)
            return false;

        $repo = new EloquentRepository(Odd::class);

        try {
            $repo->updateOrCreate(['home_team' => $data['home_team'], 'away_team' => $data['away_team'], 'date' => $data['date'],], [
                'date_time' => $data['date_time'],
                'date' => $data['date'],
                'time' => $data['time'],
                'home_team' => $data['home_team'],
                'away_team' => $data['away_team'],
                'home_win_odds' => $data['one_x_two'][0],
                'draw_odds' => $data['one_x_two'][1],
                'away_win_odds' => $data['one_x_two'][2],
                'over_odds' => $data['over_under'][0] ?? null,
                'under_odds' => $data['over_under'][1] ?? null,
                'gg_odds' => $data['gg_ng'][0] ?? null,
                'ng_odds' => $data['gg_ng'][1] ?? null,
                'game_id' => $data['game_id'] ?? null,
                'competition_id' => $data['competition_id'] ?? null,
                'source' => $data['source'] ?? null,
            ]);
        } catch (Exception $e) {
            Log::info('Odds save failed:', ['err' => $e->getMessage(), 'data' => $data]);
        }
    }

    static function saveTeams(...$args)
    {
        [$teams, $competition, $country, $is_domestic] = $args;

        $added = [];
        foreach ($teams as $team) {

            ['name' => $name, 'url' => $url] = $team[1]['team'];

            $team = Common::saveTeam($name, $url, $competition, $country, $is_domestic);

            $arr = [
                'action' => $team->action,
                'id' => $team->id,
                'name' => $team->name,
                'country' => $country->name ?? 'N/A',
                'competition' => $competition->name ?? 'N/A',
            ];

            $added[] = $arr;
        }

        return $added;
    }
    static function saveTeam(...$args)
    {
        [$name, $url, $competition, $country, $is_domestic] = $args;

        $data = [
            'name' => $name,
            'slug' => Str::slug($name),
            'url' => $url,
            'img' => '',
            'status' => 1,
            "updated_at" => date('Y-m-d H:i:s'),
            'country_id' => $country->id,
        ];

        if ($is_domestic === true)
            $data = array_merge($data, [
                'competition_id' => $competition->id,
            ]);

        $res = self::teamRepo()->model->updateOrCreate(['name' => $name, 'url' => $url], $data);

        $res->action = $res->wasRecentlyCreated === true ? 'created' : 'updated';

        return $res;
    }

    static function saveTeamLogo($team_id, $source)
    {
        $exists = self::teamRepo()->findById($team_id);
        if ($exists->img)
            return true;

        $ext = pathinfo($source, PATHINFO_EXTENSION);
        $filename = "t" . $team_id . '.' . $ext;

        $dest = "public/images/teams/" . $filename; /* Complete path & file name */

        if (Client::downloadFileFromUrl($source, $dest)) {
            self::teamRepo()->update($team_id, ['img' => $dest]);
            return true;
        }

        return false;
    }

    static function getCountrySlug($source)
    {
        $parts = explode('/', trim($source, '/'));
        $slug = $parts[1];

        if (preg_match('#^football-tips-and-predictions-for-#', $slug))
            $slug = preg_replace('#^football-tips-and-predictions-for-#', '', $slug);
        elseif (preg_match('#^tips-and-predictions-for-#', $slug))
            $slug = preg_replace('#^tips-and-predictions-for-#', '', $slug);
        elseif (preg_match('#^predictions-#', $slug))
            $slug = preg_replace('#^predictions-#', '', $slug);
        else {
            $m = 'Cannot get country from slug';
            Log::info($m . ':', ['source' => $source]);
            return false;
        }

        $slug = preg_replace('#-1-hnl$#i', '', $slug);
        $slug = preg_replace('#-j-league$#i', '', $slug);
        $slug = preg_replace('#-divizia-a$#i', '', $slug);
        $slug = preg_replace('#-veikkausliiga$#i', '', $slug);
        $slug = preg_replace('#-s-league$#i', '', $slug);
        $slug = preg_replace('#-meistriliiga$#i', '', $slug);
        $slug = preg_replace('#-major-league-soccer$#i', '', $slug);
        $slug = preg_replace('#-tippeligaen$#i', '', $slug);
        $slug = preg_replace('#-superliga$#i', '', $slug);
        $slug = preg_replace('#^usa$#i', 'united-states', $slug);

        return $slug;
    }

    static function checkCompetitionAbbreviation($games_table)
    {
        // Detailed fixture for existing games, so let's get this year's table
        $table = Carbon::now()->year . '_games';

        $game = gameModel($table);

        if ($game->wherenull('competition_id')->count() > 0) {

            $repo = new EloquentRepository(CompetitionAbbreviation::class);
            $repo->model->all();

            $game->join('competition_abbreviations', $games_table . '.competition_abbreviation', 'competition_abbreviations.name')
                ->wherenull($games_table . '.competition_id')
                ->wherenotnull('competition_abbreviations.competition_id')
                ->select(
                    'competition_abbreviations.name as competition_abbreviation',
                    'competition_abbreviations.competition_id',
                )
                ->chunk(10, function ($games) use ($table) {
                    foreach ($games as $game) {
                        $gamet = gameModel($table);
                        $gamet->where('competition_abbreviation', $game->competition_abbreviation)->update(['competition_id' => $game->competition_id]);
                    }
                });
        }
    }
}