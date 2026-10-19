<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    use HasFactory;
    protected $table = 'ai_settings';
    protected $fillable = [
        'role',
        'model'
    ];
    const MODELS = [
        'deepseek/deepseek-prover-v2:free' => 'هوش مصنوعی دیپ سیک v2',
//        'microsoft/phi-4-reasoning-plus:free' => 'هوش مصنوعی مایکروسافت',
        'google/gemini-2.5-pro-preview' => 'هوش مصنوعی Gemini 2.6 Pro',
        'deepseek/deepseek-r1-0528:free' => 'R1 هوش مصنوعی دیپ سیک '
    ];
}
