<?php

namespace App\Modules\KeuanganQu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entry_date' => 'required|date',
            'description' => 'required|string|max:500',
            'details' => 'required|array|min:2',
            'details.*.coa_account_id' => 'required|exists:coa_accounts,id',
            'details.*.description' => 'nullable|string|max:500',
            'details.*.debit' => 'nullable|integer|min:0',
            'details.*.kredit' => 'nullable|integer|min:0',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $details = $this->input('details', []);
            $totalDebit = 0;
            $totalKredit = 0;

            foreach ($details as $i => $detail) {
                $debit = (int) ($detail['debit'] ?? 0);
                $kredit = (int) ($detail['kredit'] ?? 0);

                if ($debit === 0 && $kredit === 0) {
                    $validator->errors()->add("details.{$i}", 'Baris jurnal harus memiliki nilai debit atau kredit.');
                }

                if ($debit > 0 && $kredit > 0) {
                    $validator->errors()->add("details.{$i}", 'Baris jurnal tidak boleh memiliki nilai debit dan kredit sekaligus.');
                }

                $totalDebit += $debit;
                $totalKredit += $kredit;
            }

            if ($totalDebit !== $totalKredit) {
                $validator->errors()->add('details', 'Total debit dan kredit harus sama.');
            }
        });
    }
}
