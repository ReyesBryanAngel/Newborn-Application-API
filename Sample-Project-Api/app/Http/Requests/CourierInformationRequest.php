<?php

namespace App\Http\Requests;

use App\Models\CourierInformation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourierInformationRequest extends FormRequest
{
    const STRING_REQUIRED = 'required|string';
    public function rules()
    {
        return [
            'user_id' => 'required',
            'courier' => ['required', 'string', Rule::in(CourierInformation::COURIER_OPTIONS)],
            'tracking_number' => self::STRING_REQUIRED,
            'date_of_pickup' => 'required|date',
            'notes' => 'max:255',
        ];
    }
}
