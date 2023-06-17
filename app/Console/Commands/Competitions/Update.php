<?php

namespace App\Console\Commands\Competitions;

use App\Jobs\Competitions\UpateJob;
use Illuminate\Console\Command;

class Update extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'competitions:update';

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
         (new UpateJob())->dispatch();

         return true;
    }
}
