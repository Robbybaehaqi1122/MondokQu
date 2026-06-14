<?php

namespace App\Modules\Tahfidz\Requests;

use App\Models\TahfidzRecord;
use App\Models\TahfidzSession;
use Illuminate\Foundation\Http\FormRequest;

class StoreTahfidzSessionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'santri_id' => ['required', 'exists:santris,id'],
            'session_date' => ['required', 'date'],
            'status' => ['sometimes', 'string', 'in:'.implode(',', TahfidzSession::availableStatuses())],
            'notes' => ['nullable', 'string', 'max:1000'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.surah_id' => ['required', 'exists:tahfidz_surahs,id'],
            'records.*.verse_start' => ['required', 'integer', 'min:1'],
            'records.*.verse_end' => ['required', 'integer', 'min:1', 'gte:records.*.verse_start'],
            'records.*.evaluation' => ['required', 'string', 'in:'.implode(',', TahfidzRecord::availableEvaluations())],
            'records.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'santri_id.required' => 'Pilih santri yang akan dicatat setoran hafalannya.',
            'session_date.required' => 'Tanggal setoran harus diisi.',
            'records.required' => 'Minimal satu ayat harus dicatat.',
            'records.*.surah_id.required' => 'Pilih surah untuk setiap ayat yang disetorkan.',
            'records.*.verse_start.required' => 'Ayat awal harus diisi.',
            'records.*.verse_end.required' => 'Ayat akhir harus diisi.',
            'records.*.verse_end.gte' => 'Ayat akhir harus lebih besar atau sama dengan ayat awal.',
            'records.*.evaluation.required' => 'Penilaian hafalan harus dipilih.',
        ];
    }
}
