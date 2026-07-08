<?php

namespace App\Modules\KeuanganQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\KeuanganQu\Models\CoaAccount;
use App\Modules\KeuanganQu\Models\JournalEntry;
use App\Modules\KeuanganQu\Models\JournalEntryDetail;
use App\Modules\KeuanganQu\Requests\StoreJournalEntryRequest;
use Illuminate\Http\Request;

class JournalEntryController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $entries = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            $year = (int) ($request->get('year', now()->year));
            $month = (int) ($request->get('month', now()->month));
            return view('modules.keuangan-qu.jurnal.index', compact('entries', 'year', 'month'));
        }

        $year = (int) ($request->get('year', now()->year));
        $month = (int) ($request->get('month', now()->month));

        $entries = JournalEntry::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->period($year, $month)
            ->search($request->get('search'))
            ->orderByLatest()
            ->with(['details.coaAccount', 'creator', 'approver'])
            ->paginate(15);

        return view('modules.keuangan-qu.jurnal.index', compact('entries', 'year', 'month'));
    }

    public function create()
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            return redirect()->route('keuangan.dashboard')
                ->with('error', 'Anda tidak memiliki akses ke modul ini tanpa terhubung ke pesantren.');
        }

        $accounts = CoaAccount::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type', 'normal_balance']);

        return view('modules.keuangan-qu.jurnal.create', compact('accounts'));
    }

    public function store(StoreJournalEntryRequest $request)
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        if (! $tenantId) {
            return back()->with('error', 'Anda tidak memiliki akses ke modul ini tanpa terhubung ke pesantren.');
        }

        $entryDate = $request->date('entry_date');

        $entry = JournalEntry::withoutTenantScope()->create([
            'tenant_id' => $tenantId,
            'journal_number' => JournalEntry::generateJournalNumber(
                $tenantId, $entryDate->year, $entryDate->month
            ),
            'description' => $request->description,
            'entry_date' => $entryDate,
            'period_month' => $entryDate->month,
            'period_year' => $entryDate->year,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        foreach ($request->details as $detail) {
            JournalEntryDetail::create([
                'journal_entry_id' => $entry->id,
                'coa_account_id' => $detail['coa_account_id'],
                'description' => $detail['description'] ?? null,
                'debit' => (int) ($detail['debit'] ?? 0),
                'kredit' => (int) ($detail['kredit'] ?? 0),
            ]);
        }

        activity()->log('Membuat jurnal: ' . $entry->journal_number);

        return redirect()->route('keuangan.jurnal.show', $entry)
            ->with('success', 'Jurnal berhasil dibuat.');
    }

    public function show(JournalEntry $journalEntry)
    {
        abort_if($journalEntry->tenant_id !== auth()->user()->tenant_id, 403);

        $journalEntry->loadMissing(['details.coaAccount', 'creator', 'approver']);

        return view('modules.keuangan-qu.jurnal.show', compact('journalEntry'));
    }

    public function destroy(JournalEntry $journalEntry)
    {
        abort_if($journalEntry->tenant_id !== auth()->user()->tenant_id, 403);

        if ($journalEntry->isPosted()) {
            return back()->with('error', 'Jurnal yang sudah diposting tidak bisa dihapus.');
        }

        $journalEntry->details()->delete();
        $journalEntry->delete();

        activity()->log('Menghapus jurnal: ' . $journalEntry->journal_number);

        return redirect()->route('keuangan.jurnal.index')
            ->with('success', 'Jurnal berhasil dihapus.');
    }

    public function approve(JournalEntry $journalEntry)
    {
        abort_if($journalEntry->tenant_id !== auth()->user()->tenant_id, 403);

        if ($journalEntry->isPosted()) {
            return back()->with('error', 'Jurnal sudah diposting sebelumnya.');
        }

        if (!$journalEntry->isBalanced()) {
            return back()->with('error', 'Jurnal tidak balance. Debit dan kredit harus sama.');
        }

        $journalEntry->approve(auth()->user());

        activity()->log('Menyetujui jurnal: ' . $journalEntry->journal_number);

        return redirect()->route('keuangan.jurnal.show', $journalEntry)
            ->with('success', 'Jurnal berhasil diposting.');
    }
}
