<?php
declare(strict_types=1);

namespace App\Models;

use DomainException;
use RuntimeException;

class PromptModel
{
    public function generateSuggestions(string $rawPrompt, array $previousSuggestions = []): array
    {
        $apiSuggestions = $this->requestSuggestionsFromApi($rawPrompt, $previousSuggestions);

        if ($apiSuggestions !== []) {
            return array_slice($apiSuggestions, 0, 3);
        }

        throw new DomainException('O Nexis aceita apenas prompts relacionados a problemas técnicos.');
    }

    public function generateResponse(string $prompt, array $history = []): string
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'Você é o Nexis, um especialista sênior em suporte técnico para pessoas com diferentes níveis de experiência, inclusive iniciantes e idosos. Considere técnico qualquer pedido de ajuda para usar, instalar, baixar, atualizar, configurar ou solucionar problemas em celulares, computadores, aplicativos, sites, contas digitais, sistemas operacionais, impressoras, redes, arquivos e serviços online. Também considere válidos algoritmos, matemática aplicada à computação, linguagens como C++, bibliotecas, hardware, bancos de dados, segurança e desenvolvimento. Exemplos técnicos: como baixar o Facebook, instalar um aplicativo, recuperar acesso, conectar o Wi-Fi ou ajustar o celular. Não dependa de uma lista fixa de palavras. Se o assunto não tiver relação com tecnologia ou suporte digital, explique educadamente que o Nexis atende apenas problemas técnicos. Para pedidos técnicos, produza uma solução confiável, específica e completa em português do Brasil, com linguagem simples e passos numerados. Adapte os passos ao dispositivo e sistema mencionados; se faltarem dados, declare a suposição e apresente alternativas. Não invente comandos, versões, links ou resultados. Inclua avisos de segurança contra golpes, downloads falsos e compartilhamento de senhas quando forem pertinentes. Use Markdown simples com títulos, listas, negrito e blocos de código; não use tabelas, HTML ou mostre raciocínio interno.',
            ],
        ];

        foreach (array_slice($history, -12) as $message) {
            if (!is_array($message)) {
                continue;
            }

            $role = (string) ($message['role'] ?? '');
            $content = trim((string) ($message['content'] ?? ''));

            if (in_array($role, ['user', 'assistant'], true) && $content !== '') {
                $messages[] = [
                    'role' => $role,
                    'content' => $content,
                ];
            }
        }

        $messages[] = [
            'role' => 'user',
            'content' => $prompt,
        ];

        $response = $this->requestChatCompletion($messages);

        $content = trim((string) ($response['choices'][0]['message']['content'] ?? ''));

        if ($content === '') {
            throw new RuntimeException('O serviço de IA do Nexis não retornou uma resposta válida.');
        }

        return $content;
    }

    private function requestSuggestionsFromApi(string $rawPrompt, array $previousSuggestions = []): array
    {
        $avoidList = $this->buildAvoidList($previousSuggestions);

        $response = $this->requestChatCompletion([
            [
                'role' => 'system',
                'content' => 'Você é o Nexis, um especialista em diagnóstico técnico e engenharia de prompts para pessoas com diferentes níveis de experiência, inclusive iniciantes e idosos. Considere técnico qualquer pedido de ajuda para usar, instalar, baixar, atualizar, configurar ou solucionar problemas em celulares, computadores, aplicativos, sites, contas digitais, sistemas operacionais, impressoras, redes, arquivos e serviços online. Também considere válidos algoritmos, matemática aplicada à computação, linguagens como C++, bibliotecas, hardware, bancos de dados, segurança e desenvolvimento. Exemplos técnicos: como baixar o Facebook, instalar um aplicativo, recuperar acesso, conectar o Wi-Fi ou ajustar o celular. Não dependa de uma lista fixa de palavras. Se não for técnico ou não tiver relação com tecnologia e suporte digital, responda apenas com JSON válido no formato {"suggestions":[]}. Se for técnico, gere exatamente 3 opções úteis, específicas e diferentes, em português do Brasil, com linguagem simples. Cada descrição deve ser um prompt pronto para copiar e deve conter, de forma explícita e adaptada ao caso, os cinco elementos: PERSONA (quem a IA deve ser e seu nível de especialização), AÇÃO (tarefa principal e verbo claro), RESULTADO (formato e critérios da resposta), TOM (estilo de comunicação) e SUPORTE (contexto, ambiente, restrições, exemplos ou dados que a IA deve considerar). Use rótulos curtos como "Persona:", "Ação:", "Resultado:", "Tom:" e "Suporte:" para deixar a estrutura visível. Não invente dados ausentes: transforme lacunas em perguntas ou suposições indicadas. Responda apenas com JSON válido no formato {"suggestions":[{"title":"...","description":"..."}]}. Não inclua texto fora do JSON. Evite sugestões genéricas e repetições.',
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
        $groq = (array) ($config['groq'] ?? []);
        $baseUrl = rtrim((string) ($groq['base_url'] ?? ''), '/');
        $endpoint = '/' . ltrim((string) ($groq['endpoint'] ?? '/chat/completions'), '/');
        $apiKey = (string) ($groq['api_key'] ?? '');
        $model = (string) ($groq['model'] ?? 'openai/gpt-oss-120b');
        $temperature = (float) ($groq['temperature'] ?? 0.15);
        $reasoningEffort = (string) ($groq['reasoning_effort'] ?? 'medium');
        $maxTokens = (int) ($groq['max_tokens'] ?? $maxTokens);

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
            'reasoning_effort' => $reasoningEffort,
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
        $curlError = curl_error($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if (!is_string($response) || $statusCode < 200 || $statusCode >= 300) {
            $detail = $curlError !== '' ? ' (' . $curlError . ')' : '';
            throw new RuntimeException('O serviço de IA do Nexis retornou uma resposta inválida ou indisponível.' . $detail);
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('A resposta do serviço de IA do Nexis não pôde ser processada.');
        }

        return $decoded;
    }
}
