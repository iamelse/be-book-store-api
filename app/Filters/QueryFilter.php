<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

abstract class QueryFilter
{
    protected array $params;

    public function apply(Builder $query, array $params)
    {
        $this->params = $params;

        foreach ($params as $key => $value) {
            if ($value === null || $value === '') continue;

            if (method_exists($this, $key)) {
                $this->{$key}($query, $value);
            }
        }

        return $query;
    }
}