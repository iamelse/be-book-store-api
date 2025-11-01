<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Services\ItemService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    use ApiResponse;

    private ItemService $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    public function index(Request $request)
    {
        $params = [
            'limit' => $request->get('limit', 10),
            'search' => $request->get('search'),
            'filters' => $request->only(['item_category_id']),
            'sort' => $request->get('sort', 'id:asc')
        ];

        $items = $this->itemService->getAllItems($params);

        return $this->successResponse(
            ItemResource::collection($items),
            'Items fetched successfully'
        );
    }

    public function show($id)
    {
        $item = $this->itemService->getItemById($id);

        if (!$item) {
            return $this->errorResponse('Item not found', 404);
        }

        return $this->successResponse(
            new ItemResource($item),
            'Item fetched successfully'
        );
    }
}