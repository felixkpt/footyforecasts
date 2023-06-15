<?php

use Illuminate\Support\Str;

if (!function_exists('defaultColumns')) {

    function defaultColumns($model)
    {
        // $model->uuid = Str::orderedUuid();
        $model->user_id = auth()->id() ?? Str::random(26);
        return true;
    }
}
