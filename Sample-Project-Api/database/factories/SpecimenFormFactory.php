<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SpecimenForm>
 */
class SpecimenFormFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "user_id" => fake()->uuid,
            "type_of_sample"=> fake()->randomElement(['Initial Sample', 'Repeat Sample']),
            "baby_last_name" => fake()->lastName,
            "baby_first_name" => fake()->firstName,
            "for_multiple_births" => fake()->randomElement(['2A (For Twins)', '2B (For Twins)', '3A (For Triplets)']),
            "mothers_first_name" => fake()->firstName,
            "date_and_time_of_birth" => fake()->dateTime->format('Y-m-d H:i:s.u'),
            "sex" => fake()->randomElement(['M', 'F', 'A']),
            'babys_weight_in_grams' => fake()->numberBetween(2000, 5000),
            "date_and_time_of_collection" => fake()->dateTime->format('Y-m-d H:i:s.u'),
            'age_of_gestation_in_weeks' => fake()->numberBetween(20, 40),
            "place_of_collection" => fake()->randomElement(["Hospital", "House", "Private"]),
            "place_of_birth" => fake()->randomElement(["Tondo", "Quezon City", "Isabela"]),
            "attending_practitioner" => fake()->firstName,
            "practitioner_profession" => fake()->randomElement(["nurse", "doctor", "midwife"]),
            "practitioner_profession_other" => fake()->firstName,
            "practitioners_day_contact_number" => fake()->phoneNumber,
            "practitioners_mobile_number" => fake()->phoneNumber,
            "specimens" => fake()->randomElement(["heel", "cord", "venus"]),
            "baby_status" => fake()->randomElement(["On Antibiotics", "Sick", "Normal"]),
            "baby_status_cont" => fake()->firstName,
            "name_of_parent" => fake()->firstName,
            "number_and_street" => fake()->streetAddress,
            "barangay_or_city" => fake()->city,
            "province" => "Mars",
            "zip_code" => fake()->countryCode,
            "contact_number_of_parent" => fake()->phoneNumber,
            "additional_contact_number" => fake()->phoneNumber,
            "specimen_status" => "Pending",
            "checked" => 1
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
