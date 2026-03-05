<?php

namespace App\Services;

use App\Models\Prompt;
use OpenAI;

class AiAnalyzerService
{
    public function analyzeContent($content)
    {
        $prompt = Prompt::active();

        if (!$prompt) {
            throw new \Exception('No active prompt configuration found.');
        }

        $userMessage = str_replace('{{content}}', $content, $prompt->user_prompt_template);

        try {
            $client = OpenAI::client(env('OPENAI_API_KEY'));

            $response = $client->chat()->create([
                'model' => $prompt->model_name,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $prompt->system_prompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => $userMessage,
                    ],
                ],
                'temperature' => $prompt->temperature,
            ]);

            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            throw new \Exception('AI service error: ' . $e->getMessage());
        }
    }
}
