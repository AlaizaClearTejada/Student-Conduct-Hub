<?php

namespace Database\Factories;

use App\Enums\CaseStatus;
use App\Models\TribunalCase;
use Illuminate\Database\Eloquent\Factories\Factory;

class TribunalCaseFactory extends Factory
{
    protected $model = TribunalCase::class;

    public function definition(): array
    {
        return [
            'case_number' => 'CSE-'.$this->faker->unique()->numberBetween(1000, 9999),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'status' => CaseStatus::UNDER_INFORMAL_DISCUSSION,
        ];
    }
}
