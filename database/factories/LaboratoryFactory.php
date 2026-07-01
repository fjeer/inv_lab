<?php

namespace Database\Factories;

use App\Models\Laboratory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaboratoryFactory extends Factory
{
    protected $model = Laboratory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company() . ' Lab',
            'code' => fake()->unique()->bothify('LAB-####'),
            'location' => fake()->address(),
            'capacity' => fake()->numberBetween(10, 50),
            'responsible_person_id' => User::factory(),
            'status' => 'active',
        ];
    }
}
