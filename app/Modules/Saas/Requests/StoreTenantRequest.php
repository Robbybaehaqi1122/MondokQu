<?php

namespace App\Modules\Saas\Requests;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreTenantRequest extends FormRequest
{
    /**
     * The named error bag for validation errors.
     *
     * @var string
     */
    protected $errorBag = 'createTenant';

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
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique(Tenant::class, 'slug')],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone_number' => ['nullable', 'string', 'max:30'],
            'create_owner_account' => ['nullable', 'boolean'],
            'owner_name' => ['nullable', 'string', 'max:255', 'required_if:create_owner_account,1'],
            'owner_username' => [
                'nullable',
                'string',
                'max:255',
                'required_if:create_owner_account,1',
                Rule::unique(User::class, 'username'),
            ],
            'owner_email' => [
                'nullable',
                'string',
                'lowercase',
                'email',
                'max:255',
                'required_if:create_owner_account,1',
                Rule::unique(User::class, 'email'),
            ],
            'owner_phone_number' => ['nullable', 'string', 'max:30'],
            'owner_password' => ['nullable', Password::min(8), 'required_if:create_owner_account,1', 'confirmed'],
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
            'name.required' => 'Nama tenant atau pondok wajib diisi.',
            'slug.alpha_dash' => 'Slug hanya boleh berisi huruf, angka, tanda hubung, atau underscore.',
            'slug.unique' => 'Slug tenant sudah dipakai. Gunakan slug lain yang unik.',
            'contact_email.email' => 'Email kontak tenant harus berupa alamat email yang valid.',
            'contact_phone_number.max' => 'Nomor kontak tenant maksimal 30 karakter.',
            'owner_name.required_if' => 'Nama admin tenant wajib diisi jika Anda ingin langsung membuat akun owner/admin.',
            'owner_username.required_if' => 'Username admin tenant wajib diisi jika Anda ingin langsung membuat akun owner/admin.',
            'owner_username.unique' => 'Username admin tenant sudah digunakan.',
            'owner_email.required_if' => 'Email admin tenant wajib diisi jika Anda ingin langsung membuat akun owner/admin.',
            'owner_email.email' => 'Email admin tenant harus berupa alamat email yang valid.',
            'owner_email.unique' => 'Email admin tenant sudah digunakan.',
            'owner_phone_number.max' => 'Nomor HP admin tenant maksimal 30 karakter.',
            'owner_password.required_if' => 'Password admin tenant wajib diisi jika Anda ingin langsung membuat akun owner/admin.',
            'owner_password.confirmed' => 'Konfirmasi password admin tenant harus sama.',
        ];
    }
}
