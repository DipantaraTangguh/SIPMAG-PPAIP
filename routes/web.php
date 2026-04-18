<?php

use Illuminate\Support\Facades\Route;

// React SPA Catch-all Route
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api|admin|filament|_debugbar).*$');
