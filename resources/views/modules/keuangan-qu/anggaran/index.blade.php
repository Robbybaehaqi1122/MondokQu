<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Anggaran</h2>
                <div class="text-secondary mt-1">Manajemen anggaran per pos dan realisasi.</div>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBudgetModal">
                <i class="ti ti-plus me-1"></i> Anggaran Baru
            </button>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tahun</label>
                    <select name="year" class="form-select">
                        @for ($y = now()->year + 1; $y >= now()->year - 2; $y--)
                            <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Bulan</label>
                    <select name="month" class="form-select">
                        @foreach (['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'] as $i => $m)
                            @if ($i > 0)
                                <option value="{{ $i }}" @selected($month === $i)>{{ $m }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary"><i class="ti ti-filter me-1"></i> Filter</button>
                    <a href="{{ route('keuangan.anggaran.index') }}" class="btn btn-outline-secondary"><i class="ti ti-x"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kode Akun</th>
                        <th>Nama Akun</th>
                        <th>Tipe</th>
                        <th class="text-end">Anggaran</th>
                        <th class="text-end">Realisasi</th>
                        <th class="text-end">Sisa</th>
                        <th>Progres</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($budgets as $budget)
                        @php
                            $realization = $budget->realizationAmount();
                            $remaining = $budget->budget_amount - $realization;
                            $percentage = $budget->realizationPercentage();
                            $barClass = $percentage > 100 ? 'bg-danger' : ($percentage > 85 ? 'bg-warning' : 'bg-success');
                        @endphp
                        <tr>
                            <td>{{ $budget->coaAccount->code }}</td>
                            <td>{{ $budget->coaAccount->name }}</td>
                            <td>
                                <span class="badge bg-{{ $budget->coaAccount->type === 'pendapatan' ? 'green' : 'red' }}-lt">
                                    {{ $budget->coaAccount::typeLabel($budget->coaAccount->type) }}
                                </span>
                            </td>
                            <td class="text-end">Rp {{ number_format($budget->budget_amount, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($realization, 0, ',', '.') }}</td>
                            <td class="text-end {{ $remaining < 0 ? 'text-danger fw-semibold' : '' }}">
                                Rp {{ number_format($remaining, 0, ',', '.') }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress progress-sm flex-grow-1" style="min-width: 80px;">
                                        <div class="progress-bar {{ $barClass }}" style="width: {{ min($percentage, 100) }}%"></div>
                                    </div>
                                    <span class="small {{ $percentage > 100 ? 'text-danger' : '' }}">{{ number_format($percentage, 1) }}%</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-icon btn-outline-primary btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#editBudgetModal{{ $budget->id }}"
                                        title="Edit">
                                        <i class="ti ti-pencil"></i>
                                    </button>
                                    <form action="{{ route('keuangan.anggaran.destroy', $budget) }}" method="POST"
                                        onsubmit="return confirm('Hapus anggaran ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-outline-danger btn-sm" title="Hapus">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-secondary text-center py-4">Belum ada anggaran untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($budgets->hasPages())
            <div class="card-footer">{{ $budgets->links() }}</div>
        @endif
    </div>

    {{-- Create Modal --}}
    <div class="modal modal-blur fade" id="createBudgetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('keuangan.anggaran.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Anggaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                            </div>
                        @endif
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label required">Akun</label>
                                <select name="coa_account_id" class="form-select @error('coa_account_id') is-invalid @enderror" required>
                                    <option value="">Pilih akun...</option>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}" @selected(old('coa_account_id') == $acc->id)>
                                            {{ $acc->code }} - {{ $acc->name }} ({{ $acc::typeLabel($acc->type) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required">Bulan</label>
                                <select name="period_month" class="form-select @error('period_month') is-invalid @enderror" required>
                                    @foreach (['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'] as $i => $m)
                                        <option value="{{ $i + 1 }}" @selected(old('period_month', $month) == $i + 1)>{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required">Tahun</label>
                                <select name="period_year" class="form-select @error('period_year') is-invalid @enderror" required>
                                    @for ($y = now()->year + 1; $y >= now()->year - 2; $y--)
                                        <option value="{{ $y }}" @selected(old('period_year', $year) == $y)>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required">Jumlah (Rp)</label>
                                <input type="number" name="budget_amount" class="form-control @error('budget_amount') is-invalid @enderror"
                                    value="{{ old('budget_amount') }}" min="1" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Keterangan</label>
                                <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modals --}}
    @foreach ($budgets as $budget)
        <div class="modal modal-blur fade" id="editBudgetModal{{ $budget->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('keuangan.anggaran.update', $budget) }}">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Anggaran: {{ $budget->coaAccount->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Akun</label>
                                    <input type="text" class="form-control" value="{{ $budget->coaAccount->code }} - {{ $budget->coaAccount->name }}" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Periode</label>
                                    <input type="text" class="form-control" value="{{ $budget->period_month }}/{{ $budget->period_year }}" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Jumlah (Rp)</label>
                                    <input type="number" name="budget_amount" class="form-control" value="{{ old('budget_amount', $budget->budget_amount) }}" min="1" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Keterangan</label>
                                    <textarea name="description" class="form-control" rows="2">{{ old('description', $budget->description) }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</x-app-layout>
