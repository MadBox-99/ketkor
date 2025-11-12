<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $user = User::query()->where('name', $this->name)->first();

        return [
            'name' => ['required', Rule::unique('users', 'name')->ignoreModel($user)],
            'email' => ['required', 'email'],
            'role' => ['required', Rule::exists('roles', 'name')],
            'organization' => ['nullable'],
        ];
    }
}
