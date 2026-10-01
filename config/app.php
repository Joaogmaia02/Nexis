<?php
declare(strict_types=1);

return [
    'app_name' => 'Nexis',
    'timezone' => 'America/Sao_Paulo',
    'groq' => [
        'base_url' => 'https://api.groq.com/openai/v1',
        'endpoint' => '/chat/completions',
        'api_key' => getenv('GROQ_API_KEY') ?: '',
        'model' => 'openai/gpt-oss-120b',
        'temperature' => 0.15,
        'reasoning_effort' => 'medium',
        'max_tokens' => 4096,
    ],
];
