<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UUID;

class SpecimenForm extends Model
{
    use HasFactory, UUID;

    protected $table = "specimen_forms";

    protected $fillable = [
        'checked',
        'tracking_number',
        'type_of_sample',
        'baby_last_name',
        'baby_first_name',
        'for_multiple_births',
        'mothers_first_name',
        'date_and_time_of_birth',
        'sex',
        'babys_weight_in_grams',
        'date_and_time_of_collection',
        'age_of_gestation_in_weeks',
        'place_of_collection',
        'place_of_birth',
        'attending_practitioner',
        'practitioner_profession',
        'practitioner_profession_other',
        'practitioners_day_contact_number',
        'practitioners_mobile_number',
        'specimens',
        'baby_status',
        'baby_status_cont',
        'name_of_parent',
        'number_and_street',
        'barangay_or_city',
        'province',
        'zip_code',
        'contact_number_of_parent',
        'additional_contact_number',
        'specimen_status',
        'result',
        'viewed'
    ];

    protected $hidden = ['created_at', 'updated_at', 'id'];

    const SPECIMEN_STATUS = [
        'Pending',
        'In Transit',
        'Sent',
        'Received',
    ];

    const RESULT = [
        'Normal',
        'Inadequate',
        'Elevated'
    ];

    public function feeding()
    {
        return $this->hasMany(Feeding::class);
    }
}
