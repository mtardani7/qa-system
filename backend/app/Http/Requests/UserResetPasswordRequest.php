<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserResetPasswordRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('users.update') ?? false; }

    public function rules(): array
    {
        return [
            'password' => ['required', 'confirmed', 'min:8'],
        ];
    }
}
