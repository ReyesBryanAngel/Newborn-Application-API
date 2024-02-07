<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourierInformation>
 */
class CourierInformationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fake()->uuid,
            'courier' => fake()->randomElement(['GRAB', '2GO', 'NINJAVAN', 'LBC']),
            'tracking_number' => (string) fake()->numberBetween(10000000, 99999999),
            'date_of_pickup' => fake()->dateTime->format('Y-m-d H:i:s.u'),
            'notes' => fake()->text
        ];
    }

     /**
     * Indicate that the user_id should be set to the given user's id.
     *
     * @param  \App\Models\User  $user
     * @return $this
     */
    public function forUser(User $user)
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
            ];
        });
    }
}
