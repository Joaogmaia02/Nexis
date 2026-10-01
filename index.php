<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$router = require __DIR__ . '/config/routes.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');

if ($basePath !== '' && ($path === $basePath || str_starts_with($path, $basePath . '/'))) {
    $path = substr($path, strlen($basePath)) ?: '/';
}

if ($path === '/index.php') {
	$path = '/';
}

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $path);
