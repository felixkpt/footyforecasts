<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface EloquentRepositoryInterface
 * @package App\Interfaces
 */
interface IEloquentRepository
{
    /**
     * @param array $payload
     * @return Model
     */
    public function create(array $payload): ?Model;

    /**
     * @param int $modelId
     * @param array $columns
     * @param array $relations
     * @param array $appends
     * @return Model
     */
    public function findById(
        int $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ): ?Model;

    /**
     * @return Collection
     */
    public function all(): Collection;

    /**
     * @param int $modelId
     * @param array $payload
     * @return bool
     */
    public function  update(int $modelId, array $payload): bool;

    /**
     * @param int $modelId
     * @return bool
     */
    public function deleteById(int $modelId): bool;

}
