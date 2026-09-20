<?php
declare(strict_types=1);

namespace App\Models;

use DomainException;
use RuntimeException;

class PromptModel
{
    public function generateSuggestions(string $rawPrompt, array $previousSuggestions = []): array
    {
        $this->assertTechnicalScope($rawPrompt);

        $apiSuggestions = $this->requestSuggestionsFromApi($rawPrompt, $previousSuggestions);

        if ($apiSuggestions !== []) {
            return array_slice($apiSuggestions, 0, 3);
        }

        throw new RuntimeException('O serviço de IA do Nexis não retornou sugestões válidas para este prompt.');
    }

    private function assertTechnicalScope(string $rawPrompt): void
    {
        if ($this->looksNonTechnical($rawPrompt)) {
            throw new DomainException('O Nexis aceita apenas prompts relacionados a problemas técnicos.');
        }

        if (!$this->hasTechnicalIndicators($rawPrompt)) {
            throw new DomainException('O Nexis aceita apenas prompts relacionados a problemas técnicos.');
        }
    }

    private function hasTechnicalIndicators(string $rawPrompt): bool
    {
        $prompt = mb_strtolower($rawPrompt);

        $technicalPatterns = [
            '/\b(erro|bug|falha|crash|debug|stack trace|exceção|exception|api|endpoint|request|response|json|php|javascript|html|css|sql|mysql|postgres|sqlite|servidor|apache|xampp|localhost|rede|network|firewall|dns|login|autentica|senha|cookie|sessão|sistema|código|programa|software|hardware|instala\w*|configura\w*|deploy|build|compil\w*|integra\w*|script|migr\w*|banco de dados|database|performance|latência|memory|memória|cache|token|oauth|jwt)\b/u',
        ];

        foreach ($technicalPatterns as $pattern) {
            if (preg_match($pattern, $prompt) === 1) {
                return true;
            }
        }

        return false;
    }

    private function looksNonTechnical(string $rawPrompt): bool
    {
        $prompt = mb_strtolower($rawPrompt);

        $nonTechnicalPatterns = [
            '/\b(receita|cozinhar|culinária|romance|namoro|amor|amizade|astrologia|horóscopo|esportes?|futebol|música|filme|cinema|viagem|turismo|moda|maquiagem|desenho|poesia|religião|política)\b/u',
        ];

        foreach ($nonTechnicalPatterns as $pattern) {
            if (preg_match($pattern, $prompt) === 1) {
                return true;
            }
        }

        return false;
    }

    private function requestSuggestionsFromApi(string $rawPrompt, array $previousSuggestions = []): array
    {
        $avoidList = $this->buildAvoidList($previousSuggestions);

        $response = $this->requestChatCompletion([
            [
                'role' => 'system',
                'content' => 'Você é o Nexis, um middleware técnico. Gere exatamente 3 opções de prompts inteligentes em português do Brasil, sempre relacionadas a problemas técnicos. Responda apenas com JSON válido no formato {"suggestions":[{"title":"...","description":"..."}]}. Não inclua texto fora do JSON. Se houver uma lista de sugestões já exibidas, gere novas opções diferentes delas.',
            ],
            [
                'role' => 'user',
                'content' => $rawPrompt . $avoidList,
            ],
        ]);

        $content = (string) ($response['choices'][0]['message']['content'] ?? '');
        $decoded = json_decode($content, true);

        if (!is_array($decoded) || !isset($decoded['suggestions']) || !is_array($decoded['suggestions'])) {
            return [];
        }

        return $decoded['suggestions'];
    }

    private function buildAvoidList(array $previousSuggestions): string
    {
        if ($previousSuggestions === []) {
            return '';
        }

        $titles = [];

        foreach ($previousSuggestions as $suggestion) {
            if (!is_array($suggestion)) {
                continue;
            }

            $title = trim((string) ($suggestion['title'] ?? ''));
            if ($title !== '') {
                $titles[] = $title;
            }
        }

        if ($titles === []) {
            return '';
        }

        return "\n\nEvite repetir estas opções já exibidas: " . implode(' | ', $titles) . '.';
    }

    private function requestChatCompletion(array $messages, int $maxTokens = 900): array
    {
        $config = require __DIR__ . '/../../config/app.php';
        $deepseek = (array) ($config['deepseek'] ?? []);
        $baseUrl = rtrim((string) ($deepseek['base_url'] ?? ''), '/');
        $endpoint = '/' . ltrim((string) ($deepseek['endpoint'] ?? '/chat/completions'), '/');
        $apiKey = (string) ($deepseek['api_key'] ?? '');
        $model = (string) ($deepseek['model'] ?? 'deepseek-reasoner');
        $temperature = (float) ($deepseek['temperature'] ?? 0.2);

        if ($baseUrl === '' || $apiKey === '') {
            throw new RuntimeException('Configure a conexão do serviço de IA do Nexis em config/app.php.');
        }

        if (!function_exists('curl_init')) {
            throw new RuntimeException('A extensão cURL não está disponível para acessar o serviço de IA do Nexis.');
        }

        $payload = json_encode([
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($payload === false) {
            throw new RuntimeException('Não foi possível montar a requisição para o serviço de IA do Nexis.');
        }

        $ch = curl_init($baseUrl . $endpoint);
        if ($ch === false) {
            throw new RuntimeException('Não foi possível iniciar a conexão com o serviço de IA do Nexis.');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if (!is_string($response) || $statusCode < 200 || $statusCode >= 300) {
            throw new RuntimeException('O serviço de IA do Nexis retornou uma resposta inválida ou indisponível.');
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('A resposta do serviço de IA do Nexis não pôde ser processada.');
        }

        return $decoded;
    }
}
