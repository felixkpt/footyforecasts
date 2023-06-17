<?php
namespace App\Repositories;

use App\Interfaces\ICompetitionRepository;
use App\Models\Competition;

class CompetitionRepository extends EloquentRepository implements ICompetitionRepository
{
    //@var Model
    public $model;

    //BaseEloquentRepository constructor
    public function __construct()
    {
        $this->model = new Competition();
    }

 }
