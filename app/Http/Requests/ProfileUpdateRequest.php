<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// FormRequest untuk update profil (salah satu dari dua FormRequest di aplikasi ini).
// Email/NIM/NIP harus unik, kecuali milik user yang bersangkutan.
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Aturan validasi untuk pembaruan profil.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'nim' => ['nullable', 'string', 'max:20', Rule::unique(User::class)->ignore($this->user()->id)],
            'nip' => ['nullable', 'string', 'max:20', Rule::unique(User::class)->ignore($this->user()->id)],
            'sso_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
