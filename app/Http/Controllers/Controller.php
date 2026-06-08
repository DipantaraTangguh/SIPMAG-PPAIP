<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractPaginator;

abstract class Controller
{
    protected function perPage(Request $request, int $default = 20): int
    {
        $perPage = (int) $request->query('per_page', $default);

        return max(1, min(100, $perPage));
    }

    protected function resourceCollection(Request $request, string $resourceClass, mixed $items): array
    {
        if ($items instanceof AbstractPaginator) {
            $payload = $items->toArray();
            $payload['data'] = $resourceClass::collection($items->getCollection())->resolve($request);

            return $payload;
        }

        return $resourceClass::collection($items)->resolve($request);
    }
}
