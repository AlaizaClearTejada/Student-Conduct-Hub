<?php

namespace Database\Factories;

use App\Helpers\StudentProgramCatalog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a student user with all student-specific fields populated.
     */
    public function student(): static
    {
        static $counter = 1;

        return $this->state(function (array $attributes) use (&$counter) {
            $firstName = fake()->firstName();
            $lastName = fake()->lastName();
            $college = fake()->randomElement(StudentProgramCatalog::colleges());

            return [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'name' => $firstName.' '.$lastName,
                'student_id' => '24-'.str_pad((string) $counter++, 5, '0', STR_PAD_LEFT),
                'program' => fake()->randomElement(StudentProgramCatalog::programsForCollege($college)),
                'college' => $college,
                'role_type' => 'student',
                'year_level' => fake()->randomElement(['1st Year', '2nd Year', '3rd Year', '4th Year']),
                'section' => fake()->randomElement(['A', 'B', 'C', '1A', '1B', '2A', '3B']),
            ];
        });
    }

    /**
     * Create a staff user without student-specific academic fields.
     */
    public function staff(): static
    {
        return $this->state(fn (array $attributes) => [
            'program' => null,
            'college' => null,
            'year_level' => null,
            'section' => null,
            'role_type' => 'osdw_staff',
        ]);
    }

    /**
     * Create an administrator without student-specific academic fields.
     */
    public function administrator(): static
    {
        return $this->state(fn (array $attributes) => [
            'program' => null,
            'college' => null,
            'year_level' => null,
            'section' => null,
            'role_type' => 'admin',
        ]);
    }
}
