<?php
declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\PromptController;
use App\Core\Router;

$router = new Router();

$router->get('/', HomeController::class, 'index');
$router->post('/api/prompts', PromptController::class, 'generate');

return $router;
