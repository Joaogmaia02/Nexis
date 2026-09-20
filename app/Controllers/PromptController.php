<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\PromptModel;
use DomainException;
use RuntimeException;

class PromptController extends Controller
{
    public function generate(): void
    {
        $payload = $this->readRequestPayload();
        $rawInput = $this->readPromptInput($payload);
        $previousSuggestions = $this->readPreviousSuggestions($payload);

        if ($rawInput === '') {
            $this->json([
                'success' => false,
                'message' => 'Informe um prompt para gerar sugestões.',
            ], 422);

            return;
        }

        try {
            $model = new PromptModel();
            $suggestions = $model->generateSuggestions($rawInput, $previousSuggestions);
        } catch (DomainException $exception) {
            $this->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);

            return;
        } catch (RuntimeException $exception) {
            $this->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 503);

            return;
        }

        $this->json([
            'success' => true,
            'prompt' => $rawInput,
            'suggestions' => $suggestions,
        ]);
    }

    private function readPromptInput(array $payload = []): string
    {
        if ($payload !== []) {
            return trim((string) ($payload['prompt'] ?? ''));
        }

        return trim((string) ($_POST['prompt'] ?? $_POST['message'] ?? ''));
    }

    private function readPreviousSuggestions(array $payload = []): array
    {
        if ($payload === [] || !isset($payload['previous_suggestions']) || !is_array($payload['previous_suggestions'])) {
            return [];
        }

        return $payload['previous_suggestions'];
    }

    private function readRequestPayload(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (!str_contains($contentType, 'application/json')) {
            return [];
        }

        $payload = json_decode(file_get_contents('php://input') ?: '', true);

        return is_array($payload) ? $payload : [];
    }
}
