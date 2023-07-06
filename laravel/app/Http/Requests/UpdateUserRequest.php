<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
                Rule::unique('users', 'email')->ignoreModel(Auth::user()),
            ],
            'username' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}\p{M}\p{N}._-]+$/', // Allow letters (included accented chars), numbers and special chars: ._-
                Rule::unique('users', 'username')->ignoreModel(Auth::user()),
            ],
        ];
    }
}
