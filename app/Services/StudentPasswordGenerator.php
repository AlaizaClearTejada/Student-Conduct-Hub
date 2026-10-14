<?php

namespace App\Services;

use InvalidArgumentException;

class StudentPasswordGenerator
{
    public function generate(int $length = 12): string
    {
        if ($length < 4) {
            throw new InvalidArgumentException('A student password must contain at least four characters.');
        }

        $characters = [
            $this->randomCharacter('ABCDEFGHIJKLMNOPQRSTUVWXYZ'),
            $this->randomCharacter('abcdefghijklmnopqrstuvwxyz'),
            $this->randomCharacter('0123456789'),
            $this->randomCharacter('!@#$%^&*'),
        ];
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';

        while (count($characters) < $length) {
            $characters[] = $this->randomCharacter($alphabet);
        }

        for ($index = count($characters) - 1; $index > 0; $index--) {
            $swapIndex = random_int(0, $index);
            [$characters[$index], $characters[$swapIndex]] = [$characters[$swapIndex], $characters[$index]];
        }

        return implode('', $characters);
    }

    private function randomCharacter(string $characters): string
    {
        return $characters[random_int(0, strlen($characters) - 1)];
    }
}
