<?php

namespace App\Http\Controllers\Admin\Competitions;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Team;
use App\Repositories\CompetitionRepository;
use App\Repositories\EloquentRepository;
use Inertia\Inertia;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Str;

class CompetitionsController extends Controller
{

    protected $country;
    protected $competition;

    private $repo;

    public function __construct(CompetitionRepository $repo)
    {
        $this->repo = $repo;
    }

    function index()
    {
        $countries = Country::with('competitions:id,country_id,name,img')->get()->toArray();
        return Inertia::render('Competitions/Index', compact('countries'));
    }

    function show($id)
    {
        $competition = $this->repo->findById($id, ['*'], ['teams'])->toArray();
        return Inertia::render('Competitions/Competition/Show', compact('competition'));
    }

    function create()
    {
        return Inertia::render('Competitions/Create');
    }

    function store()
    {
        request()->validate([
            'source' => 'required:url'
        ]);

        $source = request()->source;

        $arr = explode('/', $source);
        $l = count($arr);
        $parts = explode('-', $arr[$l - 3]);
        $country_slug = array_slice($parts, -1)[0];
        $country = Str::title($country_slug);
        $this->saveCountry($country);

        $browser = new HttpBrowser(HttpClient::create());

        $browser->request('GET', $source);

        $html = $browser->getResponse()->getContent();

        $crawler = new Crawler($html);

        $competition = $crawler->filter('h1.frontH')->each(fn (Crawler $node) => ['name' => $node->text(), 'img' => $node->filter('img')->attr('src')]);
        $competition = $competition[0] ?? null;

        $this->saveCompetition($competition);

        $teams = $crawler->filter('table#standings.standings tr:not(.heading)')->each(function (Crawler $node, $i) {
            $row = $node->filter('td')->each(function (Crawler $node, $i) {
                $arr = ['position', 'team', 'points', 'games_played', 'won', 'draw', 'lost', 'goals_for', 'goals_for', 'goal_difference'];

                $data = $node->text();
                if ($i === 1)
                    $data = ['name' => $node->text(), 'url' => $node->filter('a')->attr('href')];

                return [$arr[$i] => $data];
            });

            return $row;
        });

        $added = $this->saveTeams($teams);

        return response(['data' => $added]);
    }

    function saveCountry(string $country): void
    {

        $countryRepo = new EloquentRepository(Country::class);

        $this->country = $countryRepo->updateOrCreate(['name' => $country], [
            'name' => $country,
            'slug' => Str::slug($country),
            'code' => '',
            'img' => '',
            'status' => 1,
        ]);
    }

    function saveCompetition(array $competition): void
    {
        ['name' => $competition, 'img' => $img] = $competition;

        $competition = trim(preg_replace('#^Standings | table$#i', '', $competition));

        $competition = trim(preg_replace('#' . $this->country->name . '#i', '', $competition));

        $this->competition = $this->repo->updateOrCreate(['name' => $competition], [
            'name' => $competition,
            'slug' => Str::slug($competition),
            'country_id' => $this->country->id,
            'status' => 1,
        ]);

        $img = $this->saveCompetitionLogo($img);

        $this->repo->update($this->competition->id, ['img' => $img]);
    }

    function saveTeams(array $teams)
    {

        $added = [];
        foreach ($teams as $team) {

            ['name' => $name, 'url' => $url] = $team[1]['team'];

            $data = [
                'name' => $name,
                'slug' => Str::slug($name),
                'url' => $url,
                'competition_id' => $this->competition->id,
                'country_id' => $this->country->id,
                'img' => '',
                'status' => 1,
                "updated_at" => date('Y-m-d H:i:s'),
            ];

            $team = Team::updateOrCreate(['name' => $name, 'country_id' => $this->country->id], $data);

            $added[] = ['country' => $this->country->name, 'competition' => $this->competition->name, 'name' => '#' . $team->name . ' ' . $team->name];
        }

        return $added;
    }

    function saveCompetitionLogo($source)
    {

        $ext = pathinfo($source, PATHINFO_EXTENSION);
        $filename = "c-" . $this->competition->id . '.' . $ext;

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
