<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3">
            <div>
                <h2 class="page-title">Kode Akun (COA)</h2>
                <div class="text-secondary mt-1">Chart of Accounts - Daftar akun keuangan pondok.</div>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCoaModal">
                <i class="ti ti-plus me-1"></i> Akun Baru
            </button>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="Cari kode atau nama akun..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-search me-1"></i> Cari
                    </button>
                </div>
                @if (request('search'))
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('keuangan.coa.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="ti ti-x me-1"></i> Reset
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Akun</th>
                        <th>Tipe</th>
                        <th>Saldo Normal</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($accounts as $account)
                        <tr>
                            <td class="fw-semibold">{{ $account->code }}</td>
                            <td>{{ $account->name }}</td>
                            <td>
                                @php
                                    $typeColors = ['aset' => 'blue', 'kewajiban' => 'orange', 'modal' => 'purple', 'pendapatan' => 'green', 'beban' => 'red'];
                                @endphp
                                <span class="badge bg-{{ $typeColors[$account->type] ?? 'secondary' }}-lt">
                                    {{ $account::typeLabel($account->type) }}
                                </span>
                            </td>
                            <td>{{ $account->balanceLabel() }}</td>
                            <td>
                                @if ($account->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-icon btn-outline-primary btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#editCoaModal{{ $account->id }}"
                                        title="Edit">
                                        <i class="ti ti-pencil"></i>
                                    </button>
                                    <form action="{{ route('keuangan.coa.destroy', $account) }}" method="POST"
                                        onsubmit="return confirm('Hapus akun ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-outline-danger btn-sm" title="Hapus">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @foreach ($account->children as $child)
                            <tr class="table-active">
                                <td class="ps-4 fw-semibold">{{ $child->code }}</td>
                                <td class="ps-4">↳ {{ $child->name }}</td>
                                <td>
                                    <span class="badge bg-{{ $typeColors[$child->type] ?? 'secondary' }}-lt">
                                        {{ $child::typeLabel($child->type) }}
                                    </span>
                                </td>
                                <td>{{ $child->balanceLabel() }}</td>
                                <td>
                                    @if ($child->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-icon btn-outline-primary btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#editCoaModal{{ $child->id }}"
                                            title="Edit">
                                            <i class="ti ti-pencil"></i>
                                        </button>
                                        <form action="{{ route('keuangan.coa.destroy', $child) }}" method="POST"
                                            onsubmit="return confirm('Hapus akun ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-outline-danger btn-sm" title="Hapus">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" class="text-secondary text-center py-4">Belum ada akun. Silakan tambah akun baru.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Create Modal --}}
    <div class="modal modal-blur fade" id="createCoaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('keuangan.coa.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Akun Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                            </div>
                        @endif
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">Kode Akun</label>
                                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="1-1000" required>
                                @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Tipe</label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">Pilih tipe...</option>
                                    @foreach ($types as $t)
                                        <option value="{{ $t }}" @selected(old('type') === $t)>{{ ucfirst($t) }}</option>
                                    @endforeach
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label required">Nama Akun</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Saldo Normal</label>
                                <select name="normal_balance" class="form-select @error('normal_balance') is-invalid @enderror" required>
                                    <option value="">Pilih...</option>
                                    <option value="debit" @selected(old('normal_balance') === 'debit')>Debit</option>
                                    <option value="kredit" @selected(old('normal_balance') === 'kredit')>Kredit</option>
                                </select>
                                @error('normal_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Akun Induk</label>
                                <select name="parent_id" class="form-select">
                                    <option value="">-- Tidak Ada (Akun Utama) --</option>
                                    @foreach ($accounts as $parent)
                                        <option value="{{ $parent->id }}" @selected(old('parent_id') == $parent->id)>
                                            {{ $parent->code }} - {{ $parent->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" class="form-check-input" value="1" checked>
                                    <label class="form-check-label">Akun Aktif</label>
                                </div>
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
    @foreach ($accounts as $account)
        @include('modules.keuangan-qu.coa._edit-modal', ['account' => $account])
        @foreach ($account->children as $child)
            @include('modules.keuangan-qu.coa._edit-modal', ['account' => $child])
        @endforeach
    @endforeach
</x-app-layout>
