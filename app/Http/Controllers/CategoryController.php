<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\ItemCollection;
use App\Services\CategoryService;
use App\Services\ItemService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class CategoryController extends Controller
{
    use ApiResponse;

    private CategoryService $categoryService;
    private ItemService $itemService;

    public function __construct(CategoryService $categoryService, ItemService $itemService)
    {
        $this->categoryService = $categoryService;
        $this->itemService = $itemService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     summary="Get all categories",
     *     tags={"Categories"},
     *     @OA\Response(
     *         response=200,
     *         description="Categories fetched successfully"
     *     )
     * )
     */
    public function index()
    {
        $categories = $this->categoryService->getAllCategories();

        return $this->successResponse(
            CategoryResource::collection($categories),
            "Categories fetched successfully"
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories/{slug}",
     *     summary="Get category details",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", example="finance")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category fetched successfully"
     *     )
     * )
     */
    public function show(string $slug)
    {
        $category = $this->categoryService->getCategoryBySlug($slug);

        if (!$category) {
            return $this->errorResponse("Category not found", 404);
        }

        return $this->successResponse(
            new CategoryResource($category),
            "Category fetched successfully"
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories/{slug}/items",
     *     summary="Get category details + items in category",
     *     tags={"Categories"},
     *     @OA\Parameter(name="slug", in="path", required=true),
     *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer", example=10)),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="min_price", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="max_price", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="sort", in="query", @OA\Schema(type="string", example="created_at:desc")),
     *     @OA\Response(response=200, description="Category items fetched successfully")
     * )
     */
    public function showWithItems(string $slug, Request $request)
    {
        $category = $this->categoryService->getCategoryBySlug($slug);

        if (!$category) {
            return $this->errorResponse("Category not found", 404);
        }

        // inject category filter automatically
        $filters = $request->except(['page', 'limit']);
        $filters['category'] = $slug;

        $params = [
            'limit' => $request->get('limit', 10),
            'filters' => $filters,
            'sort' => $request->get('sort', 'created_at:desc'),
        ];

        $items = $this->itemService->getAllItems($params);

        return $this->successResponse([
            'category' => new CategoryResource($category),
            'items' => new ItemCollection($items, $params['filters'])
        ], "Category items fetched successfully");
    }
}