<?php

namespace App\Console\Commands\Competitions;

use App\Jobs\Competitions\Fetch\FixturesJob;
use Illuminate\Console\Command;

class FetchFixtures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'competitions.fetch:fixtures';

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
         (new FixturesJob())->dispatch();

         return true;
    }
}
