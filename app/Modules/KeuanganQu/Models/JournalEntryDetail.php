<?php

namespace App\Modules\KeuanganQu\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryDetail extends Model
{
    protected $table = 'journal_entry_details';

    protected $fillable = [
        'journal_entry_id', 'coa_account_id', 'description',
        'debit', 'kredit',
    ];

    protected function casts(): array
    {
        return [
            'debit' => 'integer',
            'kredit' => 'integer',
        ];
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function coaAccount(): BelongsTo
    {
        return $this->belongsTo(CoaAccount::class, 'coa_account_id');
    }
}
