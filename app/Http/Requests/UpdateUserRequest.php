<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Modify to check authenticated
        // TODO: Modify to check authorised / own the contact
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'given_name' => ['sometimes', 'string', 'max:64'],
            'family_name' => ['sometimes', 'nullable', 'string', 'max:64'],
            'email' => ['sometimes', 'email', 'max:128', 'unique:users,email,' . $this->user->id],
        ];
    }
}
