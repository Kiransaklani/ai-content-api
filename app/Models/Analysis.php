<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Analysis extends Model
{
    use HasFactory;
    protected $fillable = [
        'content',
        'score',
        'feedback',
        'suggestions',
        'corrections',
        'corrected_content',
        'status',
        'user_id',
    ];

    protected $casts = [
        'suggestions' => 'array',
        'corrections' => 'array',
    ];

    protected $table = 'analyses';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
