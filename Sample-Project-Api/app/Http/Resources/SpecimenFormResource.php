<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SpecimenFormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type_of_sample' => $this->type_of_sample,
            'baby_last_name' => $this->baby_last_name,
            'baby_first_name' => $this->baby_first_name,
            'for_multiple_births' => $this->for_multiple_births,
            'mothers_first_name' => $this->mothers_first_name,
            'date_and_time_of_birth' => $this->date_and_time_of_birth,
            'sex' => $this->sex,
            'feeding' => $this->feeding,
            'babys_weight_in_grams' => $this->babys_weight_in_grams,
            'date_and_time_of_collection' => $this->date_and_time_of_collection,
            'age_of_gestation_in_weeks' => $this->age_of_gestation_in_weeks,
            'place_of_collection' => $this->place_of_collection,
            'place_of_birth' => $this->place_of_birth,
            'attending_practitioner' => $this->attending_practitioner,
            'practitioner_profession' => $this->practitioner_profession,
            'practitioner_profession_other' => $this->practitioner_profession_other,
            'practitioners_day_contact_number' => $this->practitioners_day_contact_number,
            'practitioners_mobile_number' => $this->practitioners_mobile_number,
            'specimens' => $this->specimens,
            'baby_status' => $this->baby_status,
            'baby_status_cont' => $this->baby_status_cont,
            'name_of_parent' => $this->name_of_parent,
            'number_and_street' => $this->number_and_street,
            'barangay_or_city' => $this->barangay_or_city,
            'province' => $this->province,
            'zip_code' => $this->zip_code,
            'contact_number_of_parent' => $this->contact_number_of_parent,
            'additional_contact_number' => $this->additional_contact_number,
            'specimen_status' => $this->specimen_status,
            'checked' => $this->checked,
            'tracking_number' => $this->tracking_number,
            'result' => $this->result,
            'viewed' => $this->viewed,
            'updated_at' => $this->updated_at
        ];
    }
}

