<?php

namespace Database\Factories;

use App\Models\Laboratory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatrolScheduleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'laboratory_id' => Laboratory::factory(),
            'day_of_week' => fake()->randomElement(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),
            'start_time' => '08:00',
            'end_time' => '10:00',
            'status' => 'active',
        ];
    }
}
