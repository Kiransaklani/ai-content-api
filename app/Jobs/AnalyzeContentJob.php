<?php

namespace App\Jobs;

use App\Models\Analysis;
use App\Services\AiAnalyzerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Analysis $analysis
    ) {}

    public function handle(): void
    {
        try {
            $aiService = new AiAnalyzerService();
            $aiResult = $aiService->analyzeContent($this->analysis->content);

            Log::info('AI raw response', [
                'analysis_id' => $this->analysis->id,
                'raw' => $aiResult,
            ]);

            // Strip markdown code blocks if GPT wraps response in ```json ... ```
            $cleanResult = $aiResult;
            if (preg_match('/```(?:json)?\s*([\s\S]*?)```/', $cleanResult, $matches)) {
                $cleanResult = trim($matches[1]);
            }

            $decoded = json_decode($cleanResult, true);

            if (!is_array($decoded)) {
                Log::error('AI response JSON decode failed', [
                    'analysis_id' => $this->analysis->id,
                    'raw' => $aiResult,
                ]);
                throw new \Exception('Invalid JSON from AI');
            }

            $score = (int) ($decoded['score'] ?? 0);
            $suggestions = $decoded['suggestions'] ?? [];
            $corrections = $decoded['corrections'] ?? [];
            $correctedContent = $decoded['corrected_content'] ?? '';

            if (!is_array($suggestions)) {
                $suggestions = [];
            }
            if (!is_array($corrections)) {
                $corrections = [];
            }

            $this->analysis->update([
                'score' => $score,
                'feedback' => $decoded['feedback'] ?? 'No feedback',
                'suggestions' => $suggestions,
                'corrections' => $corrections,
                'corrected_content' => $correctedContent,
                'status' => 'completed',
            ]);
        } catch (\Exception $e) {
            Log::error('AI Analysis failed', [
                'analysis_id' => $this->analysis->id,
                'error' => $e->getMessage(),
            ]);

            $this->analysis->update([
                'status' => 'failed',
                'feedback' => 'Analysis failed: ' . $e->getMessage(),
            ]);
        }
    }
}
