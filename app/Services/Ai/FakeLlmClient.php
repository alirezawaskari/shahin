<?php

namespace App\Services\Ai;

use App\Contracts\Ai\LlmClientInterface;

class FakeLlmClient implements LlmClientInterface
{
    /**
     * Queued or preset text responses.
     *
     * @var array<int, string>
     */
    protected array $textResponses = [];

    /**
     * Queued or preset JSON/array responses.
     *
     * @var array<int, array<string, mixed>>
     */
    protected array $jsonResponses = [];

    /**
     * Custom dynamic handler callback.
     *
     * @var callable|null
     */
    protected $customHandler = null;

    /**
     * Recorded prompt history.
     *
     * @var array<int, array{type: string, prompt: string, options: array<string, mixed>}>
     */
    protected array $history = [];

    /**
     * Set a fixed or queued text response for the next generateText calls.
     */
    public function setFakeTextResponse(string $text): self
    {
        $this->textResponses[] = $text;
        return $this;
    }

    /**
     * Set a fixed or queued array response for the next generateJson calls.
     *
     * @param array<string, mixed> $json
     */
    public function setFakeJsonResponse(array $json): self
    {
        $this->jsonResponses[] = $json;
        return $this;
    }

    /**
     * Set a custom callback handler.
     */
    public function setResponseHandler(?callable $handler): self
    {
        $this->customHandler = $handler;
        return $this;
    }

    /**
     * Get recorded call history.
     *
     * @return array<int, array{type: string, prompt: string, options: array<string, mixed>}>
     */
    public function getHistory(): array
    {
        return $this->history;
    }

    /**
     * Clear all queued responses and recorded history.
     */
    public function reset(): self
    {
        $this->textResponses = [];
        $this->jsonResponses = [];
        $this->customHandler = null;
        $this->history = [];
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function generateText(string $prompt, array $options = []): string
    {
        $this->history[] = [
            'type' => 'text',
            'prompt' => $prompt,
            'options' => $options,
        ];

        if ($this->customHandler !== null) {
            $result = ($this->customHandler)('text', $prompt, $options);
            if (is_string($result)) {
                return $result;
            }
        }

        if (!empty($this->textResponses)) {
            return array_shift($this->textResponses);
        }

        // Smart fallback mock response
        $budget = $options['page_budget'] ?? 1;
        if ($budget > 1) {
            return "جشنواره ویژه همراه با تخفیف‌های استثنایی! از جدیدترین خدمات و محصولات ویژه ما با شرایط استثنایی بهره‌مند شوید. اطلاعات بیشتر و رزرو آنلاین در سایت لاله کارت: lalecard.ir";
        }

        return "جشنواره تخفیف ویژه! اطلاعات بیشتر: lalecard.ir";
    }

    /**
     * {@inheritDoc}
     */
    public function generateJson(string $prompt, array $options = []): array
    {
        $this->history[] = [
            'type' => 'json',
            'prompt' => $prompt,
            'options' => $options,
        ];

        if ($this->customHandler !== null) {
            $result = ($this->customHandler)('json', $prompt, $options);
            if (is_array($result)) {
                return $result;
            }
        }

        if (!empty($this->jsonResponses)) {
            return array_shift($this->jsonResponses);
        }

        // Smart fallback mock heuristic based on prompt content
        $lowerPrompt = mb_strtolower($prompt, 'UTF-8');

        $filters = [];
        $rationale = 'مخاطبان هدف بر اساس تحلیل هدف کمپین شما انتخاب شدند.';

        if (str_contains($lowerPrompt, 'بانوان') || str_contains($lowerPrompt, 'خانم') || str_contains($lowerPrompt, 'زن')) {
            $filters['gender'] = 2; // female
            $rationale = 'مخاطبان خانم برای این پیشنهاد و مناسبت شناسایی و انتخاب شدند.';
        } elseif (str_contains($lowerPrompt, 'آقایان') || str_contains($lowerPrompt, 'مرد')) {
            $filters['gender'] = 1; // male
            $rationale = 'مخاطبان آقا برای این پیشنهاد شناسایی و انتخاب شدند.';
        }

        if (str_contains($lowerPrompt, 'تهران')) {
            $filters['province_id'] = 8; // Tehran
            $rationale .= ' همچنین محدوده مکانی به استان تهران محدود شد.';
        }

        if (str_contains($lowerPrompt, 'خدمات') || str_contains($lowerPrompt, 'دسته')) {
            $filters['service_category_id'] = 1;
        }

        return [
            'filters' => $filters,
            'rationale' => $rationale,
        ];
    }
}
