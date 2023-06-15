<?php

namespace App\Repositories;

use App\Interfaces\IEloquentRepository;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class EloquentRepository implements IEloquentRepository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseEloquentRepository constructor.
     *
     * @param Model $model
     */
    public function __construct($model = null)
    {
        if (is_string($model)) {
            $this->model = new $model;
        } else
            $this->model = $model;
    }

    /**
     * @param array $payload
     * @return Model
     */
    public function create(array $payload): ?Model
    {
        $model = $this->model->create($payload);
        return $model->fresh();
    }

    /**
     * @param int $modelId
     * @param array $columns
     * @param array $relations
     * @param array $appends
     * @return Model
     */
    public function findById($modelId, array|null $columns = ['*'], array $relations = [], array $appends = []): ?Model
    {
        return $this->model->select($columns)->with($relations)->where($this->model->getKeyName() . '', '=', $modelId)->firstOrFail()->append($appends);
    }

    /**
     * @param array $columns
     * @param array $relations
     * @return Collection
     */
    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return  $this->model->with($relations)->get($columns);
    }

    /**
     * @param int|string $modelId
     * @param array $payload
     * @return bool
     */
    public function update(int|string $modelId, array $payload): bool
    {
        return $this->model->where($this->model->getKeyName(), $modelId)->firstOrFail()->update($payload);
    }

    /**
     * Create or update a record matching the attributes, and fill it with values.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateOrCreate(array $attributes, array $payload): Model
    {

        return $this->model->updateOrCreate($attributes, $payload);
    }

    /**
     * @param int|string $modelId
     * @return bool
     */
    public function deleteById(int|string $modelId): bool
    {
        return  $this->findById($modelId)->delete();
    }

    }
