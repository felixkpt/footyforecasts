<?php
namespace App\Repositories;

use App\Interfaces\ITeamRepository;
use App\Models\Team;

class TeamRepository extends EloquentRepository implements ITeamRepository
{
    //@var Model
    public $model;

    //BaseEloquentRepository constructor
    public function __construct()
    {
        $this->model = new Team();
    }

}
