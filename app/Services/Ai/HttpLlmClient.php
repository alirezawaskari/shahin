<?php

namespace App\Services\Ai;

use App\Contracts\Ai\LlmClientInterface;
use App\Models\AiSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class HttpLlmClient implements LlmClientInterface
{
    protected ?AiSetting $setting = null;
    protected FakeLlmClient $fallbackClient;

    public function __construct(?AiSetting $setting = null)
    {
        $this->setting = $setting;
        $this->fallbackClient = new FakeLlmClient();
    }

    /**
     * Resolve the current active settings from database.
     */
    protected function getActiveSetting(): ?AiSetting
    {
        if ($this->setting) {
            return $this->setting;
        }

        return AiSetting::first();
    }

    /**
     * {@inheritDoc}
     */
    public function generateText(string $prompt, array $options = []): string
    {
        $setting = $this->getActiveSetting();

        // If no active real model or no API key (and not local custom), use fallback
        if (!$setting || !$setting->is_active || (empty($setting->api_key) && !str_contains($setting->base_url ?? '', 'localhost') && !str_contains($setting->base_url ?? '', '127.0.0.1'))) {
            return $this->fallbackClient->generateText($prompt, $options);
        }

        try {
            $response = $this->sendChatCompletion($setting, $prompt, $options);
            $content = $response['choices'][0]['message']['content'] ?? null;

            if ($content !== null && trim($content) !== '') {
                return trim($content);
            }
        } catch (Throwable $e) {
            Log::error('LLM API Error (generateText): ' . $e->getMessage(), [
                'provider' => $setting->provider,
                'model' => $setting->model,
            ]);
        }

        // Graceful fallback to mock response on network or API failure
        return $this->fallbackClient->generateText($prompt, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function generateJson(string $prompt, array $options = []): array
    {
        $setting = $this->getActiveSetting();

        if (!$setting || !$setting->is_active || (empty($setting->api_key) && !str_contains($setting->base_url ?? '', 'localhost') && !str_contains($setting->base_url ?? '', '127.0.0.1'))) {
            return $this->fallbackClient->generateJson($prompt, $options);
        }

        try {
            $jsonOptions = $options;
            $jsonOptions['response_format'] = ['type' => 'json_object'];

            $response = $this->sendChatCompletion($setting, $prompt, $jsonOptions);
            $rawContent = $response['choices'][0]['message']['content'] ?? null;

            if ($rawContent) {
                // Strip markdown code fences if model returned ```json ... ```
                $clean = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', trim($rawContent));
                $parsed = json_decode($clean, true);

                if (is_array($parsed)) {
                    return $parsed;
                }
            }
        } catch (Throwable $e) {
            Log::error('LLM API Error (generateJson): ' . $e->getMessage(), [
                'provider' => $setting->provider,
                'model' => $setting->model,
            ]);
        }

        return $this->fallbackClient->generateJson($prompt, $options);
    }

    /**
     * Send chat completion request to standard OpenAI-compatible API endpoint.
     *
     * @param AiSetting $setting
     * @param string $prompt
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function sendChatCompletion(AiSetting $setting, string $prompt, array $options = []): array
    {
        $baseUrl = rtrim($setting->base_url ?: 'https://openrouter.ai/api/v1', '/');
        $endpoint = str_ends_with($baseUrl, '/chat/completions') ? $baseUrl : $baseUrl . '/chat/completions';

        $model = $options['model'] ?? $setting->model ?? 'google/gemini-2.5-pro-preview';
        $system = $options['system'] ?? ($setting->role ?: 'شما دستیار هوشمند و کپی‌رایتر پیامک فارسی در پلتفرم لاله کارت هستید.');
        $temperature = (float) ($options['temperature'] ?? $setting->temperature ?? 0.70);
        $maxTokens = (int) ($options['max_tokens'] ?? $setting->max_tokens ?? 2000);

        $messages = [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $prompt],
        ];

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ];

        if (isset($options['response_format'])) {
            $payload['response_format'] = $options['response_format'];
        }

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        // Specific provider headers
        if ($setting->provider === 'openrouter') {
            $headers['HTTP-Referer'] = config('app.url', 'https://shahin.lalecard.ir');
            $headers['X-Title'] = 'Shahin LaleCard SMS Platform';
        }

        $request = Http::withHeaders($headers)->timeout(25);

        if (!empty($setting->api_key)) {
            $request = $request->withToken($setting->api_key);
        }

        $res = $request->post($endpoint, $payload);

        if (!$res->successful()) {
            throw new \RuntimeException("LLM API returned status {$res->status()}: " . $res->body());
        }

        return $res->json() ?? [];
    }

    /**
     * Test live connection to the configured model.
     *
     * @param string $provider
     * @param string $model
     * @param string|null $apiKey
     * @param string|null $baseUrl
     * @return array{success: bool, message: string, latency_ms?: int, sample?: string, error?: string}
     */
    public static function testConnection(string $provider, string $model, ?string $apiKey, ?string $baseUrl): array
    {
        $startTime = microtime(true);

        $resolvedBaseUrl = $baseUrl ?: (AiSetting::PROVIDERS[$provider]['default_base_url'] ?? 'https://api.openai.com/v1');
        $endpoint = rtrim($resolvedBaseUrl, '/') . '/chat/completions';

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'شما دستیار ارزیابی اتصال لاله کارت هستید.'],
                ['role' => 'user', 'content' => 'یک جمله کوتاه فارسی بگو: اتصال به سیستم شاهین برقرار شد.'],
            ],
            'max_tokens' => 50,
            'temperature' => 0.2,
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($provider === 'openrouter') {
            $headers['HTTP-Referer'] = config('app.url', 'https://shahin.lalecard.ir');
            $headers['X-Title'] = 'Shahin LaleCard SMS Platform';
        }

        try {
            $req = Http::withHeaders($headers)->timeout(15);
            if (!empty($apiKey)) {
                $req = $req->withToken($apiKey);
            }

            $res = $req->post($endpoint, $payload);
            $latency = (int) round((microtime(true) - $startTime) * 1000);

            if ($res->successful()) {
                $data = $res->json();
                $sample = $data['choices'][0]['message']['content'] ?? 'اتصال موفقیت‌آمیز بود.';

                return [
                    'success' => true,
                    'message' => 'اتصال زنده به مدل با موفقیت برقرار شد.',
                    'latency_ms' => $latency,
                    'sample' => trim($sample),
                ];
            }

            return [
                'success' => false,
                'message' => "خطا در اتصال به ارائه‌دهنده (کد {$res->status()})",
                'error' => $res->body(),
                'latency_ms' => $latency,
            ];
        } catch (Throwable $e) {
            $latency = (int) round((microtime(true) - $startTime) * 1000);
            return [
                'success' => false,
                'message' => 'خطای عدم دسترسی به سرور مدل یا تایم‌اوت شبکه',
                'error' => $e->getMessage(),
                'latency_ms' => $latency,
            ];
        }
    }
}
