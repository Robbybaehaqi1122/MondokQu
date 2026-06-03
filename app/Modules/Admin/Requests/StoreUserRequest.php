<?php

namespace App\Modules\Admin\Requests;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * The named error bag for validation errors.
     *
     * @var string
     */
    protected $errorBag = 'createUser';

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
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique(User::class)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'tenant_id' => ['nullable', 'integer', Rule::exists(Tenant::class, 'id')],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'avatar' => [
                'nullable',
                File::image()
                    ->types(config('user.avatar.allowed_extensions', ['jpg', 'jpeg', 'png', 'webp']))
                    ->max((int) config('user.avatar.max_size_kb', 2048)),
                'mimes:'.implode(',', config('user.avatar.allowed_extensions', ['jpg', 'jpeg', 'png', 'webp'])),
                'dimensions:min_width='.config('user.avatar.min_width', 200)
                    .',min_height='.config('user.avatar.min_height', 200)
                    .',max_width='.config('user.avatar.max_width', 2000)
                    .',max_height='.config('user.avatar.max_height', 2000),
            ],
            'role' => ['required', 'string', Rule::exists(Role::class, 'name')
                ->where(fn ($query) => $query->where('tenant_id', $this->user()?->isSuperAdmin() ? null : $this->user()?->tenant_id)),
            ],
            'status' => ['required', 'string', Rule::in(User::availableStatuses())],
            'password' => ['required', Password::min(8), 'confirmed'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $role = (string) $this->input('role');

            if (($this->user()?->isSuperAdmin() ?? false) && $role !== 'Superadmin' && ! $this->filled('tenant_id')) {
                $validator->errors()->add('tenant_id', 'User operasional pondok wajib ditempatkan pada tenant.');
            }

            if (! ($this->user()?->isSuperAdmin() ?? false) && ! $this->user()?->tenant_id) {
                $validator->errors()->add('tenant_id', 'Akun Anda belum terhubung ke tenant pondok.');
            }
        });
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama user wajib diisi.',
            'name.max' => 'Nama user maksimal 255 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan user lain.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan user lain.',
            'tenant_id.exists' => 'Tenant yang dipilih tidak ditemukan.',
            'phone_number.max' => 'Nomor HP maksimal 30 karakter.',
            'avatar.image' => 'Avatar harus berupa file gambar.',
            'avatar.mimes' => 'Avatar hanya boleh berformat JPG, JPEG, PNG, atau WEBP.',
            'avatar.max' => 'Ukuran avatar maksimal 2 MB.',
            'avatar.dimensions' => 'Dimensi avatar minimal 200x200 px dan maksimal 2000x2000 px.',
            'role.required' => 'Role wajib dipilih.',
            'role.exists' => 'Role yang dipilih tidak valid.',
            'status.required' => 'Status user wajib dipilih.',
            'status.in' => 'Status user yang dipilih tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password harus sama.',
        ];
    }
}
