<?php

namespace App\Jobs\Competitions;

use App\Mail\CompetitionsUpdatesFinishedMail;
use App\Mail\CompetitionsUpdatesMail;
use App\Repositories\CompetitionRepository;
use App\Services\Common;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class UpateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $repo;
    protected $mailUpdates = [];
    protected $chunk = 5;


    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->repo = new CompetitionRepository();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $start = Carbon::now()->toDateTimeString();

        $testdate = Carbon::createFromDate()->now()->subDays(1);

        $competitions = $this->repo->model->where('last_fetch', '<', $testdate->toDateTimeString())->orWhereNull('last_fetch')->with('country')->orderby('last_fetch', 'asc');
        $competitions->chunk($this->chunk, function ($competitions) {
            $this->update($competitions);
        });

        $now = Carbon::now();
        $testdate = preg_replace('# after$#', '', $now->diffForHumans($start, null, false, 2)) . '';

        $message = count($this->mailUpdates) . " competitions updates finished in $testdate.\n";
        echo $message;

        Mail::to('felixkpt@gmail.com')->send((new CompetitionsUpdatesFinishedMail(['name' => 'Felix', 'message' => $message])));
    }

    function update($competitions)
    {
        $start = Carbon::now()->toDateTimeString();

        $mailUpdates = [];
        foreach ($competitions as $competition) {

            ['data' => $res] = Common::updateOrCreateCompetition($competition, $competition->country, $competition->is_domestic, null, 'array');

            $data = ['id' => $competition->id, 'name' => $competition->name, 'country' => $competition->country->name, 'action' => $res['competition']['action'], 'teams' => count($res['teams']), 'removedTeams' => count($res['removedTeams'])];
            $mailUpdates[] = $data;
            $this->mailUpdates[] = $data;
        }

        $now = Carbon::now();
        $testdate = preg_replace('# after$#', '', $now->diffForHumans($start, null, false, 2)) . '';

        $message = "Running $this->chunk per chunk updates finished in $testdate\n";
        echo $message;

        return Mail::to('felixkpt@gmail.com')->send((new CompetitionsUpdatesMail(['name' => 'Felix', 'message' => $message, 'competitions' => $mailUpdates, 'chunk' => $this->chunk])));
    }
}
