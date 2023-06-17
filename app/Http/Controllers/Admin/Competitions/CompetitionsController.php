<?php

namespace App\Http\Controllers\Admin\Competitions;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Repositories\CompetitionRepository;
use App\Services\Client;
use App\Services\Common;
use Inertia\Inertia;
use Symfony\Component\DomCrawler\Crawler;

class CompetitionsController extends Controller
{
    protected $country;
    protected $competition;
    protected $is_domestic = false;

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

        $is_domestic = false;
        if (preg_match('#/standing#', $source))
            $is_domestic = true;
        
        $source = parse_url($source);

        $source =  $source['path'];

        $country = Common::saveCountry($source);

        $html = Client::request(Common::resolve($source));

        $crawler = new Crawler($html);

        $competition = $crawler->filter('h1.frontH')->each(fn (Crawler $node) => ['src' => $source, 'name' => $node->text(), 'img' => $node->filter('img')->attr('src')]);
        $competition = $competition[0] ?? null;

        $competition = Common::saveCompetition($competition, null);

        return Common::updateOrCreateCompetition($competition, $country, $is_domestic, null);
    }
}
