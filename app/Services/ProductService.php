<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Product;

class ProductService
{
    private ProductRepository $productRepo;

    public function __construct(ProductRepository $productRepo)
    {
        $this->productRepo = $productRepo;
    }

    public function getAll(): Collection
    {
        return $this->productRepo->getAll();
    }

    public function getById(int $id): ?Product
    {
        return $this->productRepo->findById($id);
    }
}