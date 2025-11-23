<?php

namespace App\Repositories;

use App\Filters\ItemFilter;
use App\Models\Item;

class ItemRepository
{
    public function getAllItems(array $params)
    {
        $query = Item::with('category');

        (new ItemFilter)->apply($query, $params['filters'] ?? []);

        [$sortBy, $sortOrder] = explode(':', $params['sort'] ?? 'id:asc');
        $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');

        return $query->paginate($params['limit'] ?? 10);
    }

    public function getItemById($id)
    {
        return Item::with('category')->find($id);
    }

    public function getItemBySlug(string $slug): ?Item
    {
        return Item::with('category')->where('slug', $slug)->first();
    }
}