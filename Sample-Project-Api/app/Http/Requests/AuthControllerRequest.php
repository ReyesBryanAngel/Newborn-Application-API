<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthControllerRequest extends FormRequest
{
    
    public function rules(): array
    {

        return [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'email|string|required',
            'password' => 'required|string|confirmed|min:8',
        ];
    }
}
