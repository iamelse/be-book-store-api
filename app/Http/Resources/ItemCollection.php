<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ItemCollection extends ResourceCollection
{
    protected array $filters;

    public function __construct($resource, array $filters = [])
    {
        parent::__construct($resource);
        $this->filters = $filters;
    }

    public function toArray($request): array
    {
        return [
            'items' => ItemResource::collection($this->collection),

            'links' => [
                'first' => $this->resource->appends($this->filters)->url(1),
                'last'  => $this->resource->appends($this->filters)->url($this->resource->lastPage()),
                'prev'  => $this->resource->appends($this->filters)->previousPageUrl(),
                'next'  => $this->resource->appends($this->filters)->nextPageUrl(),
            ],

            'meta' => [
                'pagination' => [
                    'current_page' => $this->resource->currentPage(),
                    'last_page'    => $this->resource->lastPage(),
                    'per_page'     => $this->resource->perPage(),
                    'total'        => $this->resource->total(),
                ],
                'filters_used' => array_filter($this->filters),
                'timestamp'    => now()->toISOString(),
                'request_id'   => request()->header('X-Request-ID') ?? uniqid(),
            ]
        ];
    }
}