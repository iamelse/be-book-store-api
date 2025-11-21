<?php

namespace App\Repositories;

use App\Models\Item;

class ItemRepository
{
    public function getAllItems(array $params)
    {
        $limit = $params['limit'] ?? 10;
        $search = $params['search'] ?? null;
        $filters = $params['filters'] ?? [];
        $sort = $params['sort'] ?? 'id:asc';

        $query = Item::with('category');

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                ->orWhere('author', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Filters
        foreach ($filters as $field => $value) {
            if (!is_null($value)) {
                $query->where($field, $value);
            }
        }

        // Sorting
        [$sortBy, $sortOrder] = explode(':', $sort) + [null, null];
        $allowedSorts = ['id', 'title', 'author', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
        }

        return $query->paginate($limit);
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