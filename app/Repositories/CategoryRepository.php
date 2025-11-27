<?php

namespace App\Repositories;

use App\Models\ItemCategory;

class CategoryRepository
{
    public function getAll()
    {
        return ItemCategory::orderBy('name')->get();
    }

    public function findBySlug(string $slug)
    {
        return ItemCategory::where('slug', $slug)->first();
    }
}