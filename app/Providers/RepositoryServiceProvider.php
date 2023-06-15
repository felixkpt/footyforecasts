<?php

namespace App\Providers;

use App\Interfaces\ICompetitionRepository;
use App\Interfaces\IEloquentRepository;
use App\Interfaces\IPostRepository;
use App\Repositories\CompetitionRepository;
use App\Repositories\EloquentRepository;
use App\Repositories\PostRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        require(app_path('/Repositories/helperrepo.php'));
        $this->app->bind(IEloquentRepository::class, EloquentRepository::class);
        $this->app->bind(IPostRepository::class, PostRepository::class);
        $this->app->bind(ICompetitionRepository::class, CompetitionRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
