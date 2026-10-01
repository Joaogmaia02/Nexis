<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';

        if (!is_file($viewFile)) {
            throw new \RuntimeException('View não encontrada: ' . $view);
        }

        extract($data, EXTR_SKIP);
        require $viewFile;
    }

    protected function json(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
