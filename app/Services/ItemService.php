<?php

namespace App\Services;

use App\Models\Item;
use App\Repositories\ItemRepository;
use Illuminate\Database\Eloquent\Collection;

class ItemService
{
    private ItemRepository $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    public function getAllItems(): Collection
    {
        return $this->itemRepository->getAllItems();
    }

    public function getItemById(int $id): ?Item
    {
        return $this->itemRepository->getItemById($id);
    }
}