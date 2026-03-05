<?php

namespace Database\Seeders;

use App\Models\Prompt;
use Illuminate\Database\Seeder;

class PromptSeeder extends Seeder
{
    public function run(): void
    {
        Prompt::updateOrCreate(
            ['name' => 'Content Quality Analyzer'],
            [
                'system_prompt' => 'You are an expert English editor and content quality evaluator. You fix not just spelling but also understand what the writer INTENDED to say even when words are badly misspelled. You MUST return ONLY valid JSON with ALL required keys: score, feedback, suggestions, corrections, and corrected_content. No markdown, no code blocks, no extra text.',
                'user_prompt_template' => "Analyze the following content and return JSON with these keys:\n- score: number between 0 and 100\n- feedback: a short summary of content quality (1-2 sentences)\n- suggestions: an array of 3 short actionable improvement tips\n- corrections: array of objects for issues found. Each object MUST have: \"original\" (exact problematic word/phrase from content), \"corrected\" (fixed version), \"issue\" (type like \"Spelling error\", \"Grammar error\", \"Sentence structure\")\n- corrected_content: the FULL content completely rewritten as proper, meaningful English.\n\nCRITICAL RULES for corrected_content:\n1. Do NOT just fix spelling letter-by-letter. UNDERSTAND what the writer meant to say.\n2. If a misspelled word could match multiple words, pick the one that makes the sentence MEANINGFUL. Example: \"sleplingg dpwm\" means \"slipping down\" or \"melting down\", NOT \"sleeping down\" (which makes no sense).\n3. Fix sentence structure so every sentence is grammatically correct and makes logical sense.\n4. The corrected version should read like a native English speaker wrote it.\n5. If the original content is nonsensical, interpret the likely intended meaning.\n\nExamples of CORRECT interpretation:\n- \"fellingg dpwm\" -> \"falling down\" (NOT \"felling down\")\n- \"sleplingg dpwm\" -> \"slipping down\" or \"melting down\" (NOT \"sleeping down\")\n- \"roads a re not doing gudd\" -> \"The roads are not in good condition\" (NOT \"roads are not doing good\")\n- \"ice creame is fellingg\" -> \"the ice cream is melting\" (understand context!)\n\nExample JSON:\n{\"score\": 25, \"feedback\": \"Content has many errors.\", \"suggestions\": [\"Fix spelling\", \"Improve grammar\", \"Add context\"], \"corrections\": [{\"original\": \"a re\", \"corrected\": \"are\", \"issue\": \"Spacing error\"}, {\"original\": \"gudd\", \"corrected\": \"good condition\", \"issue\": \"Spelling error + missing context\"}], \"corrected_content\": \"The roads are not in good condition, and the ice cream is melting down.\"}\n\nContent:\n{{content}}",
                'model_name' => 'gpt-4o',
                'temperature' => 0.4,
                'is_active' => true,
            ]
        );
    }
}
