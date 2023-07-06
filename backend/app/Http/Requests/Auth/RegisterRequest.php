<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
            ],
            'username' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}\p{M}\p{N}._-]+$/', // Allow letters (included accented chars), numbers and special chars: ._-
                'unique:users,username',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],
        ];
    }
}
