<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    use HasFactory;

    protected $table = 'ai_settings';

    protected $fillable = [
        'provider',
        'model',
        'api_key',
        'base_url',
        'temperature',
        'max_tokens',
        'role',
        'is_active',
    ];

    protected $casts = [
        'temperature' => 'float',
        'max_tokens' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Supported LLM Providers.
     */
    public const PROVIDERS = [
        'gemini' => [
            'name' => 'Google Gemini',
            'default_base_url' => 'https://generativelanguage.googleapis.com/v1beta/openai',
            'icon' => 'fa-google',
            'models' => [
                'google/gemini-2.5-pro-preview' => 'Gemini 2.5 Pro Preview (توصیه شده - بالاترین دقت)',
                'google/gemini-2.0-flash' => 'Gemini 2.0 Flash (بسیار سریع و اقتصادی)',
                'gemini-1.5-pro' => 'Gemini 1.5 Pro (پیش‌فرض تولید متن طولانی)',
                'gemini-1.5-flash' => 'Gemini 1.5 Flash (سبک و سریع)',
            ],
        ],
        'deepseek' => [
            'name' => 'DeepSeek AI',
            'default_base_url' => 'https://api.deepseek.com/v1',
            'icon' => 'fa-code',
            'models' => [
                'deepseek-chat' => 'DeepSeek V3 Chat (قوی‌ترین در نگارش فارسی و بهینه)',
                'deepseek-reasoner' => 'DeepSeek R1 Reasoning (تفکر منطقی عمیق)',
                'deepseek/deepseek-r1-0528:free' => 'DeepSeek R1 (نسخه آزمایشی / Free)',
                'deepseek/deepseek-prover-v2:free' => 'DeepSeek Prover v2 (آزمایشی)',
            ],
        ],
        'openai' => [
            'name' => 'OpenAI',
            'default_base_url' => 'https://api.openai.com/v1',
            'icon' => 'fa-cube',
            'models' => [
                'gpt-4o' => 'GPT-4o (چندمنظوره پرچم‌دار)',
                'gpt-4o-mini' => 'GPT-4o Mini (سریع و اقتصادی)',
                'o3-mini' => 'o3 Mini (استدلال دقیق و محاسباتی)',
            ],
        ],
        'openrouter' => [
            'name' => 'OpenRouter (گیت‌وی جامع چندمدلی)',
            'default_base_url' => 'https://openrouter.ai/api/v1',
            'icon' => 'fa-share-alt',
            'models' => [
                'google/gemini-2.5-pro-preview' => 'OpenRouter: Gemini 2.5 Pro',
                'deepseek/deepseek-r1' => 'OpenRouter: DeepSeek R1',
                'anthropic/claude-3.5-sonnet' => 'OpenRouter: Claude 3.5 Sonnet',
                'meta-llama/llama-3.3-70b-instruct' => 'OpenRouter: Llama 3.3 70B',
            ],
        ],
        'anthropic' => [
            'name' => 'Anthropic Claude',
            'default_base_url' => 'https://api.anthropic.com/v1',
            'icon' => 'fa-lightbulb-o',
            'models' => [
                'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet (بهترین در ادراک متنی)',
                'claude-3-5-haiku-20241022' => 'Claude 3.5 Haiku (فوق‌العاده سریع)',
            ],
        ],
        'custom' => [
            'name' => 'مدل اختصاصی / محلی (Local Ollama / vLLM / سرور خصوصی)',
            'default_base_url' => 'http://localhost:11434/v1',
            'icon' => 'fa-server',
            'models' => [
                'custom-model' => 'مدل دلخواه تعریف‌شده روی سرور محلی/خصوصی',
            ],
        ],
    ];

    /**
     * Backward-compatible MODELS array for legacy references.
     */
    public const MODELS = [
        'deepseek/deepseek-prover-v2:free' => 'هوش مصنوعی دیپ سیک v2',
        'google/gemini-2.5-pro-preview' => 'هوش مصنوعی Gemini 2.5 Pro',
        'deepseek/deepseek-r1-0528:free' => 'R1 هوش مصنوعی دیپ سیک ',
        'deepseek-chat' => 'DeepSeek V3 Chat',
        'gpt-4o-mini' => 'OpenAI GPT-4o Mini',
    ];

    /**
     * Get masked API key for UI display.
     */
    public function getMaskedApiKeyAttribute(): string
    {
        if (empty($this->api_key)) {
            return '';
        }
        $len = strlen($this->api_key);
        if ($len <= 8) {
            return '••••••••';
        }
        return substr($this->api_key, 0, 4) . '••••••••' . substr($this->api_key, -4);
    }
}
