<?php

namespace App\Filters;

class ItemFilter extends QueryFilter
{
    public function search($query, $search)
    {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('author', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function category($query, $slug)
    {
        $query->whereHas('category', function ($q) use ($slug) {
            $q->where('slug', $slug);
        });
    }

    public function min_price($query, $value)
    {
        $query->where('price', '>=', $value);
    }

    public function max_price($query, $value)
    {
        $query->where('price', '<=', $value);
    }
}