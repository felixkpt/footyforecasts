<?php

namespace App\Services\Games\Traits;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Services\Client;
use App\Services\Common;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

trait Source1
{

	/**
	 * Get team's fixtures
	 * @param int|string $team_id
	 * @return mixed
	 */
	function fixtures($team_id, $last_fetch_check = true)
	{

		$team = $this->repo->findById($team_id);

		if ($last_fetch_check) {
			$last_fetch = Carbon::createFromDate($team->last_fetch);
			$now = Carbon::now();
			$testdate = $last_fetch->diffInDays($now);

			if ($team->last_fetch !== null && $testdate < 0)
				return ['type' => 'success', 'message' => 'Last fetch is less than 1 day.'];
		}

		$source = Common::resolve($team->url);
		$html = Client::request($source);
		$crawler = new Crawler($html);

		$source_team = $crawler->filter('div.moduletable>div.mptlt')->text();
		if (!preg_match('#' . $team->name . '#i', $source_team))
			return ['type' => 'error', 'message' => 'Error: team mis-match!'];

		$table = $crawler->filter('div.moduletable>div.st_scrblock .st_rmain')->first();

		return $this->saveGames($table);
	}

	/**
	 * Get game's detailed fixture
	 * @param array $game
	 * @return mixed
	 */
	function doDetailedFixture($game, $competition)
	{
		$this->existing_competition = $competition;
		$this->game = $game;

		$html = Client::request(Common::resolve($this->game['url']));

		$crawler = new Crawler($html);

		// Log::info('before stop:', [$this->game['id']]);
		$header = $crawler->filter('div.predictioncontain');
		$l = $header->filter('div.lLogo a img.matchTLogo');
		$home_team_logo = null;
		if ($l->count() === 1)
			$home_team_logo = $l->attr('src');

		$l = $header->filter('time div.date_bah');
		if ($l->count() === 0)
			return;

		$date_time = $l->text();

		$d = explode(' ', $date_time, 2);
		$date = Carbon::parse(preg_replace('#\/#', '-', $d[0]))->format('Y-m-d');
		$date_time = Carbon::parse($date)->format('Y-m-d H:i');
		if (count($d) == 2)
			$date_time = Carbon::parse($date . ' ' . $d[1])->format('Y-m-d H:i');

		$l = $header->filter('div.weather_main_pr span');
		$stadium = null;
		if ($l->count() === 1)
			$stadium = $l->text();

		$competition = $crawler->filter('center.leagpredlnk a');
		$competition_url = $competition->attr('href');
		$competition = $competition->text();

		$l = $header->filter('div.rLogo a img.matchTLogo');
		$away_team_logo = null;
		if ($l->count() === 1)
			$away_team_logo = $l->attr('src');

		$ft_results = $ht_results = null;

		$res = $crawler->filter('div#1x2_table .rcnt')->filter('.lscr_td')->first();

		if ($res->count() > 0) {
			$l = $res->filter('.lscrsp');
			$ft_results = null;
			if ($l->count() === 1)
				$ft_results = $l->text();

			$l = $res->filter('.ht_scr');
			$ht_results = null;
			if ($l->count() === 1) {
				$ht_results = $l->text();
				$ht_results = preg_replace('#\)|\(#', '', $ht_results);
			}
		}

		$one_x_two = array_slice(array_filter($crawler->filter('div#1x2_table .rcnt')->filter('.prmod .haodd span')->each(function (Crawler $node) {
			$odds = $node->text();
			if ($odds > 0 && $odds < 30)
				return $odds;
			else
				return;
		})), 0, 3);
		$over_under = array_filter($crawler->filter('div#uo_table .rcnt')->filter('.prmod .haodd span')->each(function (Crawler $node) {
			$odds = $node->text();
			if ($odds > 0 && $odds < 20)
				return $odds;
			else
				return;
		}));
		$gg_ng = array_filter($crawler->filter('div#bts_table .rcnt')->filter('.prmod .haodd span')->each(function (Crawler $node) {
			$odds = $node->text();
			if ($odds > 0 && $odds < 20)
				return $odds;
			else
				return;
		}));

		// Save head to head matches section
		$table = $crawler->filter('table.stat-content')->first();
		$saved = $this->saveGames($table);

		$data = [
			'home_team_logo' => $home_team_logo,
			'date_time' => $date_time,
			'stadium' => $stadium,
			'competition' => $competition,
			'competition_url' => $competition_url,
			'away_team_logo' => $away_team_logo,
			'ft_results' => $ft_results,
			'ht_results' => $ht_results,
			'one_x_two' => $one_x_two,
			'over_under' => $over_under,
			'gg_ng' => $gg_ng
		];

		$this->updateGame($data);

		return ['game_details' => $this->updateGame($data), 'game_h2h' => $saved];
	}

	function saveGames($table)
	{

		$saved = [];
		$table->filter('.st_row')->each(function (Crawler $node) use (&$saved) {

			$date_time = $node->filter('.st_date');
			$date_month = preg_replace('#\/#', '-', $node->filter('.st_date>div:first-child')->text());
			$year = $node->filter('.st_date>div:last-child')->text();
			$date_time = Carbon::parse($date_month . '-' . $year)->format('Y-m-d');

			$hteam = $node->filter('.st_hteam a');
			$home_team_url = $hteam->attr('href');
			$home_team = $hteam->text();

			// get detailed link
			$url = null;
			$fixture = $node->filter('a.stat_link');
			if ($fixture->count() === 1)
				$url = $fixture->attr('href');

			$results = $node->filter('.st_rescnt');
			$ft_results = $results->filter('.st_res')->text();
			$ht_results = $results->filter('.st_htscr')->text();
			$ht_results = preg_replace('#\)|\(#', '', $ht_results);

			$ateam = $node->filter('.st_ateam a');
			$away_team_url = $ateam->attr('href');
			$away_team = $ateam->text();

			$competition_abbreviation = $node->filter('.st_ltag')->text();

			$res = $this->saveGame($date_time, $home_team_url, $home_team, $ft_results, $ht_results, $away_team_url, $away_team, $url, $competition_abbreviation);
			$slug = Str::slug($res);
			if (key_exists($slug, $saved))
				$saved[$slug]['counts'] = $saved[$slug]['counts'] + 1;
			else
				$saved[$slug] = ['name' => $res, 'counts' => 1];
		});

		return $saved;
	}
}