<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', Rule::unique('users', 'name')],
            'email' => ['required', Rule::unique('users', 'email')],
            'password' => ['required'],
            'organization_id' => ['required', Rule::exists('organizations', 'id')],
        ];
    }
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('A name is required.'),
            'name.unique' => __('The name has already been taken.'),
            'email.required' => __('A email is required.'),
            'email.unique' => __('The email has already been taken.'),
            'organization_id.required' => __('A organization is required.'),
            'organization_id.exists' => __('The organization has to be exist.'),

        ];
    }
}
