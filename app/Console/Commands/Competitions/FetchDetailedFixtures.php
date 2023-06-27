<?php

namespace App\Console\Commands\Competitions;

use App\Jobs\Competitions\Fetch\DetailedFixturesJob;
use Illuminate\Console\Command;

class FetchDetailedFixtures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'competitions.fetch:detailedfixtures';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update competition';

    /**
     * Execute the console command.
     */
    public function handle()
    {
         (new DetailedFixturesJob())->dispatch();

         return true;
    }
}
