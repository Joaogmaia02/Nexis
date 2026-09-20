<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$router = require __DIR__ . '/config/routes.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($path === '/index.php') {
	$path = '/';
}

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $path);
