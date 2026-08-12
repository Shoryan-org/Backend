<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\BloodRequestUrgency;

class StoreBloodRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "blood_type" => 'required|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            "no_of_units" => 'required|integer|min:1',
            'urgency' => [
                'required',
                Rule::enum(BloodRequestUrgency::class),
            ],
            "notes" => 'nullable|string|max:1000',
            'hospital' => ['required', 'array'],
            'hospital.name' => ['required', 'string', 'max:255'],
            'hospital.latitude' => ['required', 'numeric', 'between:-90,90'],
            'hospital.longitude' => ['required', 'numeric', 'between:-180,180'],
            'hospital.address_text' => ['required', 'string', 'max:255'],
        ];
    }
}
