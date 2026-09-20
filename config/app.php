<?php
declare(strict_types=1);

return [
    'app_name' => 'Nexis',
    'timezone' => 'America/Sao_Paulo',
    'deepseek' => [
        'base_url' => 'https://api.deepseek.com',
        'endpoint' => '/chat/completions',
        'api_key' => '',
        'model' => 'deepseek-reasoner',
        'temperature' => 0.2,
        'max_tokens' => 900,
    ],
];
