<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnalysisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'score' => $this->score,
            'feedback' => $this->feedback,
            'suggestions' => is_array($this->suggestions) ? $this->suggestions : [],
            'corrections' => is_array($this->corrections) ? $this->corrections : [],
            'corrected_content' => $this->corrected_content ?? '',
            'status' => $this->status ?? 'completed',
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
