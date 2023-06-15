<?php
namespace App\Repositories;

use App\Interfaces\ICompetitionRepository;
use App\Models\Competition;

class CompetitionRepository extends EloquentRepository implements ICompetitionRepository
{
    //@var Model
    protected $model;

    //BaseEloquentRepository constructor
    public function __construct(Competition $model)
    {
        $this->model = $model;
    }

 }
