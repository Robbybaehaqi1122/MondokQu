<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdateUserProfileRequest extends FormRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var User $targetUser */
        $targetUser = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique(User::class)->ignore($targetUser)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($targetUser)],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'avatar' => [
                'nullable',
                File::image()
                    ->types(config('user.avatar.allowed_extensions', ['jpg', 'jpeg', 'png', 'webp']))
                    ->max((int) config('user.avatar.max_size_kb', 2048)),
                'dimensions:min_width='.config('user.avatar.min_width', 200)
                    .',min_height='.config('user.avatar.min_height', 200)
                    .',max_width='.config('user.avatar.max_width', 2000)
                    .',max_height='.config('user.avatar.max_height', 2000),
            ],
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone_number.max' => 'Nomor HP maksimal 30 karakter.',
            'avatar.image' => 'Avatar harus berupa file gambar.',
            'avatar.mimes' => 'Avatar hanya boleh berformat JPG, JPEG, PNG, atau WEBP.',
            'avatar.max' => 'Ukuran avatar maksimal 2 MB.',
            'avatar.dimensions' => 'Dimensi avatar minimal 200x200 px dan maksimal 2000x2000 px.',
        ];
    }
}
