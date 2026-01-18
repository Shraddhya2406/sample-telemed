<?php

namespace Database\Factories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Appointment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'doctor_id' => 1, // Replace with dynamic logic if needed
            'patient_id' => 1, // Replace with dynamic logic if needed
            'appointment_date' => $this->faker->dateTimeBetween('+1 days', '+1 month'),
            'appointment_time' => $this->faker->time('H:i:s'),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'completed']),
        ];
    }
}