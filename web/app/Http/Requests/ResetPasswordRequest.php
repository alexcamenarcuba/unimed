<?php

namespace App\Domains\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users'],
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', 'min:6']
        ];
    }

    public function messages(): array
    {
        return [
            'email.exists' => 'Este email não foi encontrado no sistema.',
            'password.confirmed' => 'As senhas não coincidem.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
        ];
    }
}
