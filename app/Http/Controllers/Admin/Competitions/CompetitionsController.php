<?php

namespace App\Http\Controllers\Admin\Competitions;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Repositories\CompetitionRepository;
use App\Services\Client;
use App\Services\Common;
use Inertia\Inertia;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Str;

class CompetitionsController extends Controller
{
    private $repo;

    public function __construct(CompetitionRepository $repo)
    {
        $this->repo = $repo;
    }

    function index()
    {
        $countries = Country::where('has_competitions', true)->with('competitions:id,country_id,name,img')->get()->toArray();
        return Inertia::render('Competitions/Index', compact('countries'));
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
        $is_domestic = request()->is_domestic;

        if ($is_domestic) {
            $source = rtrim($source, '/');
            if (!Str::endsWith($source, '/standing'))
                $source .= '/standing';
        }

        $source = parse_url($source);

        $source = $source['path'];

        $exists = $this->repo->model->where('url', $source)->first();
        if ($exists)
            return respond(['data' => ['message' => 'Whoops! It seems the competition is already saved (#' . $exists->id . ').']]);

        if (!Country::count())
            return respond(['data' => ['message' => 'Countries list is empty.']]);

        $country = Common::saveCountry($source);

        $html = Client::request(Common::resolve($source));

        $crawler = new Crawler($html);

        $competition = $crawler->filter('h1.frontH')->each(fn(Crawler $node) => ['src' => $source, 'name' => $node->text(), 'img' => $node->filter('img')->attr('src')]);
        $competition = $competition[0] ?? null;

        $competition = Common::saveCompetition($competition, null, $is_domestic);

        if ($competition)
            return Common::updateCompetitionAndHandleTeams($competition, $country, $is_domestic, null, false);
        else
            return respond(['data' => ['message' => 'Cannot get competition.']]);
    }
}