<?php

namespace App\Services;

use App\Models\EmailLog;
use Illuminate\Http\JsonResponse;
use OpenAI\Laravel\Facades\OpenAI;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmailGeneratorService
{
    private const MODEL = 'gpt-4o-mini';

    private const SYSTEM_PROMPT = 'You are a professional email writer. Given a subject and context, write a clear, well-structured, and professional email. Include an appropriate greeting, body, and sign-off. Do not include placeholder names—use generic professional sign-offs like "Best regards" without a specific name.';

    public function generate(string $subject, string $context, float $temperature, int $maxTokens): JsonResponse
    {
        $result = OpenAI::chat()->create([
            'model' => self::MODEL,
            'messages' => $this->buildMessages($subject, $context),
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ]);

        $email = $result->choices[0]->message->content;
        $promptTokens = $result->usage->promptTokens;
        $completionTokens = $result->usage->completionTokens;
        $totalTokens = $result->usage->totalTokens;
        $cost = EmailLog::calculateCost($promptTokens, $completionTokens);

        EmailLog::create([
            'subject' => $subject,
            'context' => $context,
            'response' => $email,
            'model' => self::MODEL,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $totalTokens,
            'cost' => $cost,
            'streamed' => false,
        ]);

        return response()->json([
            'email' => $email,
            'usage' => [
                'prompt_tokens' => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens' => $totalTokens,
            ],
            'cost' => '$' . number_format($cost, 4),
            'model' => self::MODEL,
        ]);
    }

    public function stream(string $subject, string $context, float $temperature, int $maxTokens): StreamedResponse
    {
        return new StreamedResponse(function () use ($subject, $context, $temperature, $maxTokens) {
            $stream = OpenAI::chat()->createStreamed([
                'model' => self::MODEL,
                'messages' => $this->buildMessages($subject, $context),
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
                'stream_options' => ['include_usage' => true],
            ]);

            $fullResponse = '';

            foreach ($stream as $response) {
                $content = $response->choices[0]->delta->content ?? '';

                if ($content !== '') {
                    $fullResponse .= $content;
                    echo 'data: ' . json_encode(['chunk' => $content]) . "\n\n";
                    ob_flush();
                    flush();
                }

                if ($response->usage !== null) {
                    $promptTokens = $response->usage->promptTokens;
                    $completionTokens = $response->usage->completionTokens;
                    $totalTokens = $response->usage->totalTokens;
                    $cost = EmailLog::calculateCost($promptTokens, $completionTokens);

                    EmailLog::create([
                        'subject' => $subject,
                        'context' => $context,
                        'response' => $fullResponse,
                        'model' => self::MODEL,
                        'temperature' => $temperature,
                        'max_tokens' => $maxTokens,
                        'prompt_tokens' => $promptTokens,
                        'completion_tokens' => $completionTokens,
                        'total_tokens' => $totalTokens,
                        'cost' => $cost,
                        'streamed' => true,
                    ]);

                    echo 'data: ' . json_encode([
                        'done' => true,
                        'usage' => [
                            'prompt_tokens' => $promptTokens,
                            'completion_tokens' => $completionTokens,
                            'total_tokens' => $totalTokens,
                        ],
                        'cost' => '$' . number_format($cost, 4),
                    ]) . "\n\n";
                    ob_flush();
                    flush();
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function buildMessages(string $subject, string $context): array
    {
        return [
            ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
            ['role' => 'user', 'content' => "Subject: {$subject}\n\nContext: {$context}"],
        ];
    }
}
