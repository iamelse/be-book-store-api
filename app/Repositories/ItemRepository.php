<?php

namespace App\Repositories;

use App\Models\Item;

class ItemRepository
{
    public function getAllItems()
    {
        return Item::all();
    }

    public function getItemById($id)
    {
        return Item::find($id);
    }
}