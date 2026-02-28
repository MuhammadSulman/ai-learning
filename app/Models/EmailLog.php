<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'subject',
        'context',
        'response',
        'model',
        'temperature',
        'max_tokens',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'cost',
        'streamed',
    ];

    protected function casts(): array
    {
        return [
            'temperature' => 'float',
            'streamed' => 'boolean',
            'cost' => 'float',
        ];
    }

    /**
     * Calculate cost based on token usage for GPT-4o-mini.
     * Pricing: $0.15/1M input tokens, $0.60/1M output tokens.
     */
    public static function calculateCost(int $promptTokens, int $completionTokens): float
    {
        $inputCost = ($promptTokens / 1_000_000) * 0.15;
        $outputCost = ($completionTokens / 1_000_000) * 0.60;

        return $inputCost + $outputCost;
    }
}
