<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    protected function perPage(Request $request, int $default = 20): int
    {
        $perPage = (int) $request->query('per_page', $default);
        return max(1, min(100, $perPage));
    }
}
