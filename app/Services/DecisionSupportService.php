<?php

namespace App\Services;

use App\Models\DecisionRule;

class DecisionSupportService
{
    /**
     * Analyzes the complaint text against active decision rules and returns recommendations.
     *
     * @param  string  $complaint  The text of the incident report/complaint.
     */
    public function analyze(string $complaint): array
    {
        $text = strtolower($complaint);
        $matches = [];

        // Eager load keywords to prevent N+1 queries
        $rules = DecisionRule::with('keywords')->where('is_active', true)->get();

        foreach ($rules as $rule) {
            $score = 0;
            $matchedKeywords = [];

            foreach ($rule->keywords as $keywordObj) {
                $keywordStr = strtolower($keywordObj->keyword);

                // If the keyword is found in the complaint text
                if (str_contains($text, $keywordStr)) {
                    $score += $keywordObj->weight;
                    $matchedKeywords[] = $keywordObj->keyword;
                }
            }

            if ($score >= $rule->threshold) {
                $matches[] = [
                    'violation' => $rule->violation_name,
                    'severity' => $rule->severity,
                    'recommendation' => $rule->recommended_action,
                    'score' => $score,
                    'matched_keywords' => $matchedKeywords,
                ];
            }
        }

        // Sort matches by score descending
        usort($matches, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return [
            'matches' => $matches,
            'top_recommendation' => $matches[0] ?? null,
        ];
    }
}
