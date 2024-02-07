<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SpecimenForm;
use Illuminate\Http\Response;
use Tests\TestCase;

class SpecimenTrackingControllerTest extends TestCase
{
    /** @test */
    public function CreateSampleTest()
    {
        $mockUser = User::factory()->create();
        $token = auth()->login($mockUser);
        $sampleFactory = SpecimenForm::factory()->forUser($mockUser)->create()->toArray();

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson(route('sample.create'), $sampleFactory)
          ->assertStatus(Response::HTTP_OK)
          ->assertJsonStructure([
            "status",
            "message",
            "specimen_id"
          ]);
    }

    /** @test */
    public function updateSampleTest()
    {
        $mockUser = User::factory()->create();
        $token = auth()->login($mockUser);
        $sampleFactory = SpecimenForm::factory()->forUser($mockUser)->create()->toArray();
        $sampleForm = SpecimenForm::where('user_id', $mockUser->id)->first();
        $updatedSample = array_merge($sampleFactory, $this->updatedSampleValue($mockUser));

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson(route('sample.update', $sampleForm->id), $updatedSample)
          ->assertStatus(Response::HTTP_OK)
          ->assertJsonStructure([
            "status",
            "message"
          ]);
    }

    /** @test */
    public function deleteSampleTest()
    {
        $mockUser = User::factory()->create();
        $token = auth()->login($mockUser);
        SpecimenForm::factory()->forUser($mockUser)->create()->toArray();
        $sampleForm = SpecimenForm::where('user_id', $mockUser->id)->first();
       
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson(route('sample.delete', $sampleForm->id))
          ->assertStatus(Response::HTTP_OK)
          ->assertJsonStructure([
            "status",
            "message"
          ]);
    }

    /** @test */
    public function createFeedingTest()
    {
        $mockUser = User::factory()->create();
        $token = auth()->login($mockUser);
        SpecimenForm::factory()->forUser($mockUser)->create()->toArray();
        $sampleForm = SpecimenForm::where('user_id', $mockUser->id)->first();

        $feedingMockData = ["feedings" => ["Lactose", "Breast"]];
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson(route('feeding.create', $sampleForm->id), $feedingMockData)
          ->assertStatus(Response::HTTP_CREATED)
          ->assertJsonStructure([
            "status",
            "message"
          ]);
    }

    /** @test */
    public function updateFeedingTest()
    {
        $mockUser = User::factory()->create();
        $token = auth()->login($mockUser);
        SpecimenForm::factory()->forUser($mockUser)->create()->toArray();
        $sampleForm = SpecimenForm::where('user_id', $mockUser->id)->first();

        $feedingMockData = ["feedings" => ["Lactose", "Soy/Lactose-Free"]];
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson(route('feeding.update', $sampleForm->id), $feedingMockData)
          ->assertStatus(Response::HTTP_OK)
          ->assertJsonStructure([
            "status",
            "message"
          ]);
    }


    private function updatedSampleValue($mockUser)
    {
        return [
            "user_id" => $mockUser->id,
            "type_of_sample"=> "Initial Sample",
            "baby_last_name" => "Reyes",
            "baby_first_name" => "Angel Bryan",
            "for_multiple_births" => "3A (For Triplets)",
            "mothers_first_name" => "May",
            "date_and_time_of_birth" => "2023-12-13 07:30:00",
            "sex" => "F",
            "babys_weight_in_grams" => 5,
            "date_and_time_of_collection" => "2023-12-15 12:00:00",
            "age_of_gestation_in_weeks" => 1,
            "place_of_collection" => "Cavite",
            "place_of_birth" => "Tondo Hospital",
            "attending_practitioner" => "May",
            "practitioner_profession" => "nurse",
            "practitioner_profession_other" => null,
            "practitioners_day_contact_number" => "09263124214",
            "practitioners_mobile_number" => "09184067584",
            "specimens" => "heel",
            "baby_status" => "On Antibiotics",
            "baby_status_cont" => null,
            "name_of_parent" => "Angelo",
            "number_and_street" => "Miralles St.",
            "barangay_or_city" => "Nasugbu",
            "province" => "Batangas",
            "zip_code" => "4231",
            "contact_number_of_parent" => "09365718472",
            "additional_contact_number" => "09365748129",
            "specimen_status" => "Pending",
            "checked" => 1
        ];
    }
}
