<?php

namespace App\Interfaces;

interface IPostRepository extends IEloquentRepository {
    public function externalPost();
}
