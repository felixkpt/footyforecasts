<?php
namespace App\Repositories;

use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\IPostRepository;
use App\Models\Database\Customer;
use App\Models\Post;

class PostRepository extends EloquentRepository implements IPostRepository
{
    //@var Model
    protected $model;

    //BaseEloquentRepository constructor
    public function __construct(Post $model)
    {
        $this->model = $model;
    }

    public function externalPost()
    {
        // TODO: Implement externalPost() method.
    }
}
