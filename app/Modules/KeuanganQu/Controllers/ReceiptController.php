<?php

namespace App\Modules\KeuanganQu\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\KeuanganQu\Models\JournalEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $entries = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            return view('modules.keuangan-qu.kwitansi.index', compact('entries'));
        }

        $entries = JournalEntry::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('status', 'posted')
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->with(['details.coaAccount', 'creator'])
            ->paginate(20);

        return view('modules.keuangan-qu.kwitansi.index', compact('entries'));
    }

    public function pdf(JournalEntry $journalEntry)
    {
        abort_if($journalEntry->tenant_id !== auth()->user()->tenant_id, 403);

        if (!$journalEntry->isPosted()) {
            return back()->with('error', 'Kwitansi hanya bisa dicetak untuk jurnal yang sudah diposting.');
        }

        $journalEntry->load(['details.coaAccount', 'creator', 'approver']);

        $pdf = Pdf::loadView('exports.pdf.keuangan-qu.kwitansi', [
            'entry' => $journalEntry,
        ]);

        return $pdf->stream('kwitansi-' . $journalEntry->journal_number . '.pdf');
    }

    public function download(JournalEntry $journalEntry)
    {
        abort_if($journalEntry->tenant_id !== auth()->user()->tenant_id, 403);

        if (!$journalEntry->isPosted()) {
            return back()->with('error', 'Kwitansi hanya bisa dicetak untuk jurnal yang sudah diposting.');
        }

        $journalEntry->load(['details.coaAccount', 'creator', 'approver']);

        $pdf = Pdf::loadView('exports.pdf.keuangan-qu.kwitansi', [
            'entry' => $journalEntry,
        ]);

        return $pdf->download('kwitansi-' . $journalEntry->journal_number . '.pdf');
    }
}
