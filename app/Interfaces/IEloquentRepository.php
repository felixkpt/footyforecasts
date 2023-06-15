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
        $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ): ?Model;

    /**
     * @return Collection
     */
    public function all(): Collection;

    /**
     * @param int|string $modelId
     * @param array $payload
     * @return bool
     */
    public function update(int|string $modelId, array $payload): bool;

    /**
     * Create or update a record matching the attributes, and fill it with values.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateOrCreate(array $attributes, array $payload): Model;

    /**
     * @param int|string $modelId
     * @return bool
     */
    public function deleteById(int|string $modelId): bool;

}
