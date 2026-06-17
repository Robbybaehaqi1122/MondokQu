<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Jurnal Baru</h2>
            <div class="text-secondary mt-1">Buat jurnal transaksi harian.</div>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('keuangan.jurnal.store') }}" id="journalForm">
        @csrf
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">Tanggal Transaksi</label>
                        <input type="date" name="entry_date" class="form-control @error('entry_date') is-invalid @enderror"
                            value="{{ old('entry_date', date('Y-m-d')) }}" required max="{{ date('Y-m-d') }}">
                        @error('entry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label required">Deskripsi</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" required>{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Detail Jurnal</h3>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addRow()">
                    <i class="ti ti-plus me-1"></i> Tambah Baris
                </button>
            </div>
            @error('details')
                <div class="card-body py-2">
                    <div class="alert alert-danger mb-0">{{ $message }}</div>
                </div>
            @enderror
            <div class="table-responsive">
                <table class="table table-vcenter card-table" id="detailsTable">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Akun</th>
                            <th style="width: 25%;">Deskripsi</th>
                            <th style="width: 15%;">Debit (Rp)</th>
                            <th style="width: 15%;">Kredit (Rp)</th>
                            <th style="width: 10%;"></th>
                        </tr>
                    </thead>
                    <tbody id="detailsBody">
                        <tr>
                            <td>
                                <select name="details[0][coa_account_id]" class="form-select account-select" required>
                                    <option value="">Pilih akun...</option>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}" data-type="{{ $acc->type }}">
                                            {{ $acc->code }} - {{ $acc->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="details[0][description]" class="form-control" placeholder="Keterangan (opsional)">
                            </td>
                            <td>
                                <input type="number" name="details[0][debit]" class="form-control debit-input" min="0" step="1" value="0"
                                    oninput="toggleDebitKredit(this)">
                            </td>
                            <td>
                                <input type="number" name="details[0][kredit]" class="form-control kredit-input" min="0" step="1" value="0"
                                    oninput="toggleDebitKredit(this)">
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>
                                <select name="details[1][coa_account_id]" class="form-select account-select" required>
                                    <option value="">Pilih akun...</option>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}" data-type="{{ $acc->type }}">
                                            {{ $acc->code }} - {{ $acc->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="details[1][description]" class="form-control" placeholder="Keterangan (opsional)">
                            </td>
                            <td>
                                <input type="number" name="details[1][debit]" class="form-control debit-input" min="0" step="1" value="0"
                                    oninput="toggleDebitKredit(this)">
                            </td>
                            <td>
                                <input type="number" name="details[1][kredit]" class="form-control kredit-input" min="0" step="1" value="0"
                                    oninput="toggleDebitKredit(this)">
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="table-active fw-semibold">
                            <td colspan="2" class="text-end">Total:</td>
                            <td id="totalDebit">Rp 0</td>
                            <td id="totalKredit">Rp 0</td>
                            <td></td>
                        </tr>
                        <tr id="balanceRow" class="d-none">
                            <td colspan="5" class="text-center">
                                <span id="balanceStatus" class="badge bg-success">✓ Balance</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('keuangan.jurnal.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i> Simpan Jurnal
                </button>
            </div>
        </div>
    </form>
</x-app-layout>

@push('scripts')
<script>
let rowIndex = 2;

function toggleDebitKredit(input) {
    const row = input.closest('tr');
    const debit = row.querySelector('.debit-input');
    const kredit = row.querySelector('.kredit-input');
    if (input === debit && parseFloat(debit.value) > 0) {
        kredit.value = 0;
        kredit.readOnly = true;
    } else if (input === kredit && parseFloat(kredit.value) > 0) {
        debit.value = 0;
        debit.readOnly = true;
    } else if (parseFloat(debit.value) === 0 && parseFloat(kredit.value) === 0) {
        kredit.readOnly = false;
        debit.readOnly = false;
    }
    updateTotals();
}

function updateTotals() {
    let totalDebit = 0, totalKredit = 0;
    document.querySelectorAll('.debit-input').forEach(inp => {
        totalDebit += parseFloat(inp.value) || 0;
    });
    document.querySelectorAll('.kredit-input').forEach(inp => {
        totalKredit += parseFloat(inp.value) || 0;
    });
    document.getElementById('totalDebit').textContent = 'Rp ' + totalDebit.toLocaleString('id-ID');
    document.getElementById('totalKredit').textContent = 'Rp ' + totalKredit.toLocaleString('id-ID');

    const balanceRow = document.getElementById('balanceRow');
    const balanceStatus = document.getElementById('balanceStatus');
    if (totalDebit > 0 || totalKredit > 0) {
        balanceRow.classList.remove('d-none');
        if (totalDebit === totalKredit) {
            balanceStatus.className = 'badge bg-success';
            balanceStatus.textContent = '✓ Balance (Debit = Kredit)';
        } else {
            balanceStatus.className = 'badge bg-danger';
            balanceStatus.textContent = '✗ Tidak Balance (Selisih: Rp ' + Math.abs(totalDebit - totalKredit).toLocaleString('id-ID') + ')';
        }
    } else {
        balanceRow.classList.add('d-none');
    }
}

function addRow() {
    const tbody = document.getElementById('detailsBody');
    const row = document.createElement('tr');
    const accountOptions = document.querySelector('.account-select')?.innerHTML || '';
    row.innerHTML = `
        <td>
            <select name="details[${rowIndex}][coa_account_id]" class="form-select account-select" required>
                ${accountOptions}
            </select>
        </td>
        <td>
            <input type="text" name="details[${rowIndex}][description]" class="form-control" placeholder="Keterangan (opsional)">
        </td>
        <td>
            <input type="number" name="details[${rowIndex}][debit]" class="form-control debit-input" min="0" step="1" value="0"
                oninput="toggleDebitKredit(this)">
        </td>
        <td>
            <input type="number" name="details[${rowIndex}][kredit]" class="form-control kredit-input" min="0" step="1" value="0"
                oninput="toggleDebitKredit(this)">
        </td>
        <td>
            <button type="button" class="btn btn-icon btn-outline-danger btn-sm" onclick="this.closest('tr').remove(); updateTotals();">
                <i class="ti ti-x"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
    rowIndex++;
}
</script>
@endpush
