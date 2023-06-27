<?php

namespace App\Jobs\Competitions\Fetch;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

trait FetchTrait
{

    public function commonHandle()
    {

        $testdate = Carbon::createFromDate()->now()->subMinutes($this->last_fetch_minutes)->toDateTimeString();

        $this->repo->model
            // ->where('competitions.id', '01h3r29d2h4td8q7092mdzzyzk')
            ->where($this->last_fetch_col, '<=', $testdate)->orWhereNull($this->last_fetch_col)
            ->with('country')
            ->orderby($this->last_fetch_col, 'asc')
            ->chunk($this->chunk, function ($competitions) {
                static $counter = 0;

                $counter += $competitions->count();

                $this->update($competitions);

                if ($counter >= $this->limit) return false;

                // end competitions because we have $this->update is done 
                if ($this->withinTimeLimit($competitions->last()) === false) return false;
            });

        $now = Carbon::now();
        $duration = preg_replace('# after$#', '', $now->diffForHumans($this->start, null, false, 2)) . '';

        return $duration;
    }


    /**
     * Checks if the given competition is within the time limit.
     * If it's not within the time limit, updates the competition's last fetch time and logs the time difference.
     *
     * @param \App\Models\Competition $competition  The competition to check
     * @return bool  True if within time limit, False otherwise
     */
    function withinTimeLimit($competition)
    {
        $now = Carbon::now(); // Get the current time

        if (!$now->between($this->start, $this->stop)) {
            // Let us update competition and team last fetch to give other competitions an opportunity too
            $competition->update([$this->last_fetch_col => $now]);

            $date1 = Carbon::parse($this->start);
            $date2 = Carbon::parse($this->stop);
            $diff = $date2->diffForHumans($date1, true);

            $last_fetch_col = $this->last_fetch_col;

            Log::info('Fixtures TimeLimit:', ['time' => $diff, 'competition_data' => ['id' => $competition->id, 'name' => $competition->name, $last_fetch_col => $competition->$last_fetch_col]]);

            return false;
        }

        return true;
    }
}
