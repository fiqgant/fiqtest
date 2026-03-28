<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Auto-detect Laravel root (works locally and on shared hosting)
$appRoot = __DIR__ . '/..';         // Standard: vendor satu level di atas public/
if (!is_dir($appRoot . '/vendor')) {
    $appRoot = __DIR__;             // Shared hosting: full app ada di public_html
    if (!is_dir($appRoot . '/vendor')) {
        $appRoot = '/home/fiqte869/repositories/fiqtest'; // cPanel git path
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $appRoot . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $appRoot . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $appRoot . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
