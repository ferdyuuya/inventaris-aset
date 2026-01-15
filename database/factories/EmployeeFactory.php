<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nik' => $this->faker->unique()->numerify('##########'),
            'name' => $this->faker->name(),
            'gender' => $this->faker->randomElement(['Laki-Laki', 'Perempuan']),
            'phone' => $this->faker->phoneNumber(),
            'position' => $this->faker->randomElement([
                'Manager',
                'Supervisor', 
                'Staff IT',
                'Koordinator',
                'Sekretaris',
                'Teknisi',
                'Asisten Manager',
                'Kepala Divisi'
            ]),
        ];
    }
}
