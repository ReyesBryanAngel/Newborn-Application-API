<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\SpecimenForm;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class SpecimenTrackingRequest extends FormRequest
{
    const STRING_REQUIRED = 'required|string';
    public function rules()
    {
        $repeat_sample = $this->input('type_of_sample') !== "Repeat Sample";
        

        return [
            'user_id' => 'required',
            'type_of_sample' => $repeat_sample ? self::STRING_REQUIRED : 'nullable',
            'baby_last_name' => $repeat_sample ? self::STRING_REQUIRED : 'nullable',
            'baby_first_name' => $repeat_sample ? self::STRING_REQUIRED : 'nullable',
            'for_multiple_births' => $repeat_sample ? self::STRING_REQUIRED : 'nullable',
            'mothers_first_name' => $repeat_sample ? self::STRING_REQUIRED : 'nullable',
            'date_and_time_of_birth' => $repeat_sample ? 'required|date' : 'nullable',
            'sex' => $repeat_sample ? 'required|in:M,F,A' : 'nullable',
            'babys_weight_in_grams' => $repeat_sample ? 'required|numeric' : 'nullable',
            'date_and_time_of_collection' => $repeat_sample ? 'required|date' : 'nullable',
            'age_of_gestation_in_weeks' => $repeat_sample ? 'required|integer|min:0': 'nullable',
            'place_of_collection' => $repeat_sample ? self::STRING_REQUIRED : 'nullable',
            'place_of_birth' => $repeat_sample ? self::STRING_REQUIRED : 'nullable',
            'attending_practitioner' => $repeat_sample ? self::STRING_REQUIRED : 'nullable',
            'practitioner_profession' => $repeat_sample ? self::STRING_REQUIRED : 'nullable',
            'practitioner_profession_other' => [Rule::requiredIf($this->input("practitioner_profession") === "other" && $repeat_sample), 'nullable'],
            'practitioners_day_contact_number' => $repeat_sample ? self::STRING_REQUIRED : 'nullable',
            'practitioners_mobile_number' => $repeat_sample ? self::STRING_REQUIRED : 'nullable',
            'specimens' => $repeat_sample ? self::STRING_REQUIRED : 'nullable',
            'baby_status' => $repeat_sample ? self::STRING_REQUIRED : 'nullable',
            'baby_status_cont' => $repeat_sample ? [
                Rule::requiredIf(function () {
                    $requiredValues = [
                        'Date of Blood Transfusion',
                        'Combination of above, please state',
                        'Other Relevant Clinical Information',
                    ];
                    return in_array($this->input('baby_status'), $requiredValues);
                }),
            ] : 'nullable',
            'name_of_parent' => $repeat_sample ? self::STRING_REQUIRED : 'nullable',
            'number_and_street' => $repeat_sample ? self::STRING_REQUIRED : 'nullable',
            'barangay_or_city' => $repeat_sample ? self::STRING_REQUIRED : 'nullable',
            'province' => $repeat_sample ? self::STRING_REQUIRED : 'nullable',
            'zip_code' => $repeat_sample ? self::STRING_REQUIRED : 'nullable',
            'contact_number_of_parent' => $repeat_sample ? self::STRING_REQUIRED : 'nullable',
            'additional_contact_number' => $repeat_sample ? self::STRING_REQUIRED : 'nullable',
            'specimen_status' => $repeat_sample ? 'nullable' : [Rule::in(SpecimenForm::SPECIMEN_STATUS)],
            'result' => $repeat_sample ? 'nullable' :[Rule::in(SpecimenForm::RESULT)],
            'checked' => 'boolean',
            'tracking_number' => 'nullable',
            'viewed' => 'boolean'

        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422);

        throw new ValidationException($validator, $response);
    }
}
