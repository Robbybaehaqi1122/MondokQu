<?php

namespace App\Modules\Tahfidz\Requests;

use App\Models\TahfidzTarget;
use Illuminate\Foundation\Http\FormRequest;

class StoreTahfidzTargetRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'santri_id' => ['required', 'exists:santris,id'],
            'target_type' => ['required', 'string', 'in:' . implode(',', [
                TahfidzTarget::TYPE_JUZ,
                TahfidzTarget::TYPE_SURAH,
                TahfidzTarget::TYPE_AYAT,
            ])],
            'target_value' => ['required', 'integer', 'min:1', 'max:99999'],
            'target_date' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'santri_id.required' => 'Pilih santri.',
            'target_type.required' => 'Pilih jenis target.',
            'target_value.required' => 'Isi nilai target.',
            'target_value.min' => 'Nilai target minimal 1.',
            'target_value.max' => 'Nilai target maksimal 99999.',
            'target_date.after_or_equal' => 'Tanggal target harus hari ini atau setelahnya.',
        ];
    }
}
