<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemCollection;
use App\Http\Resources\ItemResource;
use App\Services\ItemService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=5),
 *     @OA\Property(property="name", type="string", example="Finance"),
 *     @OA\Property(property="slug", type="string", example="finance"),
 *     @OA\Property(property="description", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="Item",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="category", ref="#/components/schemas/Category"),
 *     @OA\Property(property="title", type="string", example="Sample Book Title"),
 *     @OA\Property(property="slug", type="string", example="sample-book"),
 *     @OA\Property(property="author", type="string", example="John Doe"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="price", type="number", example=120000),
 *     @OA\Property(property="stock", type="integer", example=10),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="ItemPaginationMeta",
 *     type="object",
 *     @OA\Property(property="current_page", type="integer"),
 *     @OA\Property(property="last_page", type="integer"),
 *     @OA\Property(property="per_page", type="integer"),
 *     @OA\Property(property="total", type="integer")
 * )
 *
 * @OA\Schema(
 *     schema="ItemPaginationLinks",
 *     type="object",
 *     @OA\Property(property="first", type="string"),
 *     @OA\Property(property="last", type="string"),
 *     @OA\Property(property="prev", type="string", nullable=true),
 *     @OA\Property(property="next", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="ItemData",
 *     type="object",
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/Item")),
 *     @OA\Property(property="filters_used", type="object"),
 *     @OA\Property(property="links", ref="#/components/schemas/ItemPaginationLinks"),
 *     @OA\Property(property="meta", ref="#/components/schemas/ItemPaginationMeta")
 * )
 *
 * @OA\Schema(
 *     schema="ItemCollectionResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Items fetched successfully"),
 *     @OA\Property(property="data", ref="#/components/schemas/ItemData")
 * )
 *
 * @OA\Schema(
 *     schema="ItemResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Item fetched successfully"),
 *     @OA\Property(property="data", ref="#/components/schemas/Item")
 * )
 */

class ItemController extends Controller
{
    use ApiResponse;

    private ItemService $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/items",
     *     summary="Get paginated list of items",
     *     tags={"Items"},
     *
     *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer", example=10)),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string", example="Harry Potter")),
     *     @OA\Parameter(name="category", in="query", @OA\Schema(type="string", example="finance")),
     *     @OA\Parameter(name="min_price", in="query", @OA\Schema(type="integer", example=10000)),
     *     @OA\Parameter(name="max_price", in="query", @OA\Schema(type="integer", example=50000)),
     *     @OA\Parameter(name="sort", in="query", @OA\Schema(type="string", example="price:desc")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Items fetched successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ItemCollectionResponse")
     *     )
     * )
     */
    public function index(Request $request)
    {
        $params = [
            'limit'   => $request->get('limit', 10),
            'filters' => $request->except(['page', 'limit', 'sort']),
            'sort'    => $request->get('sort', 'id:asc'),
        ];

        return $this->successResponse(
            new ItemCollection($this->itemService->getAllItems($params), $params['filters']),
            "Items fetched successfully"
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/items/{slug}",
     *     summary="Get a single item by slug",
     *     tags={"Items"},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string", example="harry-potter")),
     *     @OA\Response(response=200, description="Success", @OA\JsonContent(ref="#/components/schemas/ItemResponse")),
     *     @OA\Response(response=404, description="Item not found")
     * )
     */
    public function show(string $slug)
    {
        $item = $this->itemService->getItemBySlug($slug);

        if (!$item) {
            return $this->errorResponse('Item not found', 404);
        }

        return $this->successResponse(new ItemResource($item), "Item fetched successfully");
    }
}
