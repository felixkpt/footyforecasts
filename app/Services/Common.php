<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Odd;
use App\Models\Stadium;
use App\Repositories\CompetitionRepository;
use App\Repositories\EloquentRepository;
use App\Repositories\TeamRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

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

    static function updateOrCreateCompetition($competition, $country, $is_domestic, $crawler = null, $responseType = null)
    {

        $last_fetch = Carbon::createFromDate($competition->last_fetch);
        $now = Carbon::now();
        $testdate = $last_fetch->diffInDays($now);

        if ($competition->last_fetch !== null && $testdate < 1) return respond(['data' => ['type' => 'success', 'message' => 'Last fetch is less than 1 day.']], 200, $responseType);

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

        $action = wasCreated($competition) === true ? 'created' : 'updated';

        self::competitionRepo()->update($competition->id, ['last_fetch' => Carbon::now()]);

        return respond(['data' => ['competition' => array_merge($competition->toArray(), compact('action')), 'teams' => $added, 'removedTeams' => $removedTeams]], 200, $responseType);
    }

    static function saveCompetition(string|array $source, string $name = null)
    {

        $img = null;
        if (is_array($source)) {
            ['src' => $source, 'name' => $name, 'img' => $img]  = $source;
        }

        $source = ltrim($source, '/');
        if (!Str::startsWith($source, 'en/'))
            $source = 'en/' . $source;

        $source = ltrim($source, '/');
        if (!Str::startsWith($source, 'en/'))
            $source = 'en/' . $source;

        $country = self::saveCountry($source);

        $source = preg_replace('#/+#', '/', '/' . $source);

        $exists = self::competitionRepo()->model->where('country_id', $country->id)->where('url', $source)->first();
        if ($exists) return $exists;

        $name_init = $name;

        if ($img === null) {
            $html = Client::request(self::resolve($source));
            $crawler = new Crawler($html);

            $header = $crawler->filter('.contentmiddle h1.frontH');

            $img = $header->filter('img')->attr('src');
            $name = $header->filter('span')->text();
        }

        if (!preg_match('#' . $name_init . '#i', $name))  return false;

        $name = preg_replace('#Free football predictions for ' . $country->name . ' #i', '', $name);
        $name = preg_replace('#Standings ' . $country->name . ' #i', '', $name);
        $name = trim(preg_replace('# table$#i', '', $name));

        $competition = self::competitionRepo()->updateOrCreate(
            [
                'name' => $name,
                'url' => $source,
            ],
            [
                'name' => $name,
                'slug' => Str::slug($name),
                'country_id' => $country->id,
                'url' => $source,
                'status' => 1,
            ]
        );

        $img = self::saveCompetitionLogo($img, $country, $competition);

        return self::competitionRepo()->updateOrCreate(['id' => $competition->id], ['img' => $img]);
    }

    static function saveCompetitionLogo($source, $country, $competition)
    {

        $ext = pathinfo($source, PATHINFO_EXTENSION);
        $filename = "c" . $competition->id . '.' . $ext;

        $dest = ("images/competitions/" . $country->slug); /* Path */
        File::ensureDirectoryExists(storage_path("app/public/" . $dest));

        $dest .= '/' . $filename; /* Complete file name */

        /* Copy the file */
        copy($source, storage_path("app/public/" . $dest));

        return $dest;
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
        if (!$country) {
            $country = $repo->create([
                'name' => Str::title($slug),
                'slug' => $slug,
            ]);
        }
        return $country;
    }

    static function saveTeamLogo($team_id, $source)
    {
        $exists = self::teamRepo()->findById($team_id);
        if ($exists->img) return true;

        $ext = pathinfo($source, PATHINFO_EXTENSION);
        $filename = "t" . $team_id . '.' . $ext;

        $dest = ("images/teams"); /* Path */
        File::ensureDirectoryExists(storage_path("app/public/" . $dest));

        $dest .= '/' . $filename; /* Complete file name */

        /* Copy the file */
        copy($source, storage_path("app/public/" . $dest));

        self::teamRepo()->update($team_id, ['img' => $dest]);

        return true;
    }
    static function saveStadium($name)
    {
        $repo = new EloquentRepository(Stadium::class);

        $res = $repo->updateOrCreate(['name' => $name], [
            'name' => $name
        ]);

        return $res;
    }

    static function saveOdds(...$args)
    {

        [$one_x_two, $over_under, $gg_ng, $game_id] = $args;

        if (count($one_x_two) !== 3) return false;

        $repo = new EloquentRepository(Odd::class);

        $repo->updateOrCreate(['game_id' => $game_id], [
            'game_id' => $game_id,
            'home_win_odds' => $one_x_two[0],
            'draw_odds' => $one_x_two[1],
            'away_win_odds' => $one_x_two[2],
            'over_odds' => $over_under[0] ?? null,
            'under_odds' => $over_under[1] ?? null,
            'gg_odds' => $gg_ng[0] ?? null,
            'ng_odds' => $gg_ng[1] ?? null,

        ]);
    }

    static function saveTeams(...$args)
    {
        [$teams, $competition, $country, $is_domestic] = $args;

        $added = [];
        foreach ($teams as $team) {

            ['name' => $name, 'url' => $url] = $team[1]['team'];

            $team = Common::saveTeam($name, $url, $competition, $country, $is_domestic);

            $arr = ['id' => $team->id, 'name' => $team->name, 'country' => $country->name ?? 'N/A', 'competition' => $competition->name ?? 'N/A',];

            $action = wasCreated($team) === true ? 'created' : 'updated';

            $added[] = array_merge(['action' => $action], $arr);
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
        ];

        if ($is_domestic === true)
            $data = array_merge($data, [
                'competition_id' => $competition->id,
                'country_id' => $country->id,
            ]);

        $res = self::teamRepo()->updateOrCreate(['name' => $name, 'url' => $url], $data);

        return $res;
    }
    static function getCountrySlug($source)
    {
        $parts = explode('/', trim($source, '/'));
        $slug = $parts[1];

        if (!preg_match('#football-tips-and-predictions-for-#', $slug)) return;

        $slug = preg_replace('#football-tips-and-predictions-for-#', '', $slug);

        return $slug;
    }
}
