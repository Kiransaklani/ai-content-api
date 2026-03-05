<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prompt extends Model
{
    use HasFactory;

    protected $table = 'prompts';

    protected $fillable = [
        'name',
        'system_prompt',
        'user_prompt_template',
        'model_name',
        'temperature',
        'is_active',
    ];

    protected $casts = [
        'temperature' => 'float',
        'is_active' => 'boolean',
    ];

    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }
}
