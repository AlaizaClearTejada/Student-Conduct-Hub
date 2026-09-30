<?php

namespace Database\Seeders;

use App\Models\DecisionRule;
use Illuminate\Database\Seeder;

class DecisionSupportSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'violation_name' => 'Academic Dishonesty',
                'description' => 'Cheating on exams, plagiarism, or copying assignments.',
                'severity' => 'High',
                'recommended_action' => 'Refer to Tribunal for review',
                'threshold' => 1,
                'keywords' => [
                    ['keyword' => 'cheating', 'weight' => 5],
                    ['keyword' => 'cheated', 'weight' => 5],
                    ['keyword' => 'copied answers', 'weight' => 5],
                    ['keyword' => 'plagiarism', 'weight' => 5],
                    ['keyword' => 'copying', 'weight' => 4],
                    ['keyword' => 'exam', 'weight' => 2],
                    ['keyword' => 'suspicious', 'weight' => 1],
                ],
            ],
            [
                'violation_name' => 'Bullying / Harassment',
                'description' => 'Any act of bullying or harassment.',
                'severity' => 'Medium',
                'recommended_action' => 'Warning / Investigation',
                'threshold' => 1,
                'keywords' => [
                    ['keyword' => 'bullying', 'weight' => 5],
                    ['keyword' => 'bullied', 'weight' => 5],
                    ['keyword' => 'harassment', 'weight' => 5],
                    ['keyword' => 'harassed', 'weight' => 5],
                    ['keyword' => 'insult', 'weight' => 3],
                    ['keyword' => 'name calling', 'weight' => 2],
                ],
            ],
            [
                'violation_name' => 'Threat / Intimidation',
                'description' => 'Threatening behavior or intimidation.',
                'severity' => 'High',
                'recommended_action' => 'Immediate Tribunal Review',
                'threshold' => 1,
                'keywords' => [
                    ['keyword' => 'threat', 'weight' => 5],
                    ['keyword' => 'threatened', 'weight' => 5],
                    ['keyword' => 'intimidation', 'weight' => 5],
                    ['keyword' => 'intimidated', 'weight' => 5],
                    ['keyword' => 'scared', 'weight' => 2],
                ],
            ],
            [
                'violation_name' => 'Property Damage',
                'description' => 'Damaging or destroying property.',
                'severity' => 'Medium',
                'recommended_action' => 'Investigation / Restitution',
                'threshold' => 1,
                'keywords' => [
                    ['keyword' => 'vandalism', 'weight' => 5],
                    ['keyword' => 'damaged', 'weight' => 5],
                    ['keyword' => 'destroyed', 'weight' => 5],
                    ['keyword' => 'property damage', 'weight' => 5],
                    ['keyword' => 'broken', 'weight' => 3],
                ],
            ],
            [
                'violation_name' => 'Theft',
                'description' => 'Stealing property.',
                'severity' => 'High',
                'recommended_action' => 'Investigation',
                'threshold' => 1,
                'keywords' => [
                    ['keyword' => 'stolen', 'weight' => 5],
                    ['keyword' => 'theft', 'weight' => 5],
                    ['keyword' => 'stole', 'weight' => 5],
                    ['keyword' => 'missing', 'weight' => 2],
                ],
            ],
            [
                'violation_name' => 'Attendance Violation',
                'description' => 'Tardiness or unexcused absences.',
                'severity' => 'Low',
                'recommended_action' => 'Warning',
                'threshold' => 1,
                'keywords' => [
                    ['keyword' => 'late', 'weight' => 5],
                    ['keyword' => 'tardy', 'weight' => 5],
                    ['keyword' => 'attendance', 'weight' => 4],
                    ['keyword' => 'absent', 'weight' => 5],
                ],
            ],
        ];

        foreach ($rules as $ruleData) {
            $keywords = $ruleData['keywords'];
            unset($ruleData['keywords']);

            $rule = DecisionRule::create($ruleData);

            foreach ($keywords as $keyword) {
                $rule->keywords()->create($keyword);
            }
        }
    }
}
