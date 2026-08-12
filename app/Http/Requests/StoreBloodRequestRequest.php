<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBloodRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
              $valid=$request->validate([
            "blood_type"=>'required|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            "no_of_units"=>'required|integer|min:1',
             "hospital_name"=>'required|string',
              "longitude"=>'required|string',
              "latitude"=>'required|string',
              "urgency"=>'required|string|in:EMERGENCY,URGENT,PLANNED',
              "address_text"=>'required|string',
              "notes"=>'nullable|string',
        ]);
        ];
    }
}
