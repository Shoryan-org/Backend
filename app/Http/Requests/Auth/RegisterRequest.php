<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class RegisterRequest extends FormRequest
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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'name' => ['required', 'string', 'max:255'],

            'phone' => ['required', 'string', 'max:20', 'phone:EG,mobile', 'unique:' . User::class],
            'blood_type' => ['required', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],

            'address' => ['required', 'array'],
            'address.latitude' => ['required', 'numeric', 'between:-90,90'],
            'address.longitude' => ['required', 'numeric', 'between:-180,180'],
            'address.address_text' => ['required', 'string', 'max:255'],

        ];
    }
}
