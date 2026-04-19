<?php

namespace App\Http\Requests\Santri;

use App\Models\Santri;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreSantriRequest extends FormRequest
{
    protected const INDONESIAN_PHONE_REGEX = '/^(?:\+62|62|0)[0-9]{8,15}$/';

    /**
     * The named error bag for validation errors.
     *
     * @var string
     */
    protected $errorBag = 'createSantri';

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
            'nis' => ['required', 'string', 'max:50', Rule::unique(Santri::class)],
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', Rule::in(Santri::availableGenders())],
            'birth_place' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'address' => ['required', 'string'],
            'guardian_name' => ['nullable', 'string', 'max:255', 'required_with:guardian_phone_number'],
            'father_name' => ['required', 'string', 'max:255'],
            'mother_name' => ['required', 'string', 'max:255'],
            'guardian_phone_number' => ['nullable', 'string', 'max:20', 'regex:'.self::INDONESIAN_PHONE_REGEX, 'required_with:guardian_name'],
            'emergency_contact' => ['required', 'string', 'max:20', 'regex:'.self::INDONESIAN_PHONE_REGEX],
            'entry_date' => ['required', 'date', 'after_or_equal:birth_date', 'before_or_equal:today'],
            'entry_year' => ['required', 'integer', 'digits:4', 'min:1900', 'max:'.now()->year],
            'room_name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', Rule::in(Santri::availableStatuses())],
            'photo' => [
                'nullable',
                File::image()
                    ->types(config('santri.photo.allowed_extensions', ['jpg', 'jpeg', 'png', 'webp']))
                    ->max((int) config('santri.photo.max_size_kb', 2048)),
                'dimensions:min_width='.config('santri.photo.min_width', 200)
                    .',min_height='.config('santri.photo.min_height', 200)
                    .',max_width='.config('santri.photo.max_width', 2000)
                    .',max_height='.config('santri.photo.max_height', 2000),
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
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah digunakan santri lain.',
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'birth_place.required' => 'Tempat lahir wajib diisi.',
            'birth_date.required' => 'Tanggal lahir wajib diisi.',
            'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'address.required' => 'Alamat wajib diisi.',
            'guardian_name.required_with' => 'Wali / penanggung jawab wajib diisi jika No. HP wali / penanggung jawab diisi.',
            'father_name.required' => 'Nama ayah wajib diisi.',
            'mother_name.required' => 'Nama ibu wajib diisi.',
            'guardian_phone_number.required_with' => 'No. HP wali / penanggung jawab wajib diisi jika nama wali / penanggung jawab diisi.',
            'guardian_phone_number.max' => 'No. HP wali maksimal 20 karakter.',
            'guardian_phone_number.regex' => 'No. HP wali / penanggung jawab harus berupa nomor yang valid, diawali 0, 62, atau +62 dan hanya berisi angka setelah kode awal.',
            'emergency_contact.required' => 'Kontak darurat wajib diisi.',
            'emergency_contact.max' => 'Kontak darurat maksimal 20 karakter.',
            'emergency_contact.regex' => 'Kontak darurat harus berupa nomor yang valid, diawali 0, 62, atau +62 dan hanya berisi angka setelah kode awal.',
            'entry_date.required' => 'Tanggal masuk wajib diisi.',
            'entry_date.after_or_equal' => 'Tanggal masuk tidak boleh lebih awal dari tanggal lahir.',
            'entry_date.before_or_equal' => 'Tanggal masuk tidak boleh melebihi hari ini.',
            'entry_year.required' => 'Angkatan atau tahun masuk wajib diisi.',
            'entry_year.integer' => 'Angkatan atau tahun masuk harus berupa angka.',
            'entry_year.digits' => 'Angkatan atau tahun masuk harus 4 digit.',
            'entry_year.min' => 'Angkatan atau tahun masuk tidak valid.',
            'entry_year.max' => 'Angkatan atau tahun masuk tidak boleh melebihi tahun ini.',
            'room_name.required' => 'Kamar atau asrama wajib diisi.',
            'notes.max' => 'Catatan singkat maksimal 1000 karakter.',
            'status.required' => 'Status santri wajib dipilih.',
            'photo.image' => 'Foto harus berupa file gambar.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
            'photo.dimensions' => 'Dimensi foto minimal 200x200 px dan maksimal 2000x2000 px.',
        ];
    }
}
