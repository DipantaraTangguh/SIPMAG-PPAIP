<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Laravel cek mode maintenance dulu sebelum boot penuh.
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Composer autoload wajib masuk sebelum app jalan.
require __DIR__.'/../vendor/autoload.php';

// Dari sini request mulai di-handle Laravel.
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
