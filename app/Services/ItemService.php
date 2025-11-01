<?php

namespace App\Services;

use App\Models\Item;
use App\Repositories\ItemRepository;

class ItemService
{
    private ItemRepository $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    public function getAllItems(array $params)
    {
        return $this->itemRepository->getAllItems($params);
    }

    public function getItemById(int $id): ?Item
    {
        return $this->itemRepository->getItemById($id);
    }
}