<x-app-layout>
    @php
        $statusBadgeClasses = [
            'active' => 'bg-success-lt text-success',
            'leave' => 'bg-warning-lt text-warning',
            'exited' => 'bg-warning-lt text-warning',
            'alumni' => 'bg-azure-lt text-azure',
        ];

        $genderBadgeClasses = [
            'male' => 'bg-blue-lt text-blue',
            'female' => 'bg-pink-lt text-pink',
        ];
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="page-title">Manajemen Santri</h2>
            <div class="text-secondary mt-1">Kelola data inti santri untuk operasional pondok dari satu halaman.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 w-100">
                        <div>
                            <h3 class="card-title">Data Santri</h3>
                            <div class="text-secondary small mt-2">
                                Menampilkan {{ $santris->total() }} dari total {{ $allSantriCount }} santri.
                            </div>
                        </div>

                        @if ($canCreateSantri)
                            <div class="d-flex align-items-center">
                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    id="open-create-santri-modal"
                                    data-bs-toggle="modal"
                                    data-bs-target="#createSantriModal"
                                >
                                    <i class="ti ti-user-plus me-1"></i>
                                    Tambah Santri
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('santri.index') }}" class="row g-3 align-items-end">
                        <div class="col-lg-5">
                            <label for="q" class="form-label">Cari Santri</label>
                            <input
                                id="q"
                                name="q"
                                type="text"
                                class="form-control"
                                value="{{ $filters['q'] }}"
                                placeholder="Cari NIS, nama, atau wali"
                            >
                        </div>

                        <div class="col-md-3 col-lg-2">
                            <label for="gender_filter" class="form-label">Jenis Kelamin</label>
                            <select id="gender_filter" name="gender" class="form-select form-select-pretty">
                                <option value="">Semua</option>
                                @foreach ($genders as $gender)
                                    <option value="{{ $gender['value'] }}" @selected($filters['gender'] === $gender['value'])>
                                        {{ $gender['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 col-lg-2">
                            <label for="status_filter" class="form-label">Status</label>
                            <select id="status_filter" name="status" class="form-select form-select-pretty">
                                <option value="">Semua</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status['value'] }}" @selected($filters['status'] === $status['value'])>
                                        {{ $status['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                                <a href="{{ route('santri.index') }}" class="btn btn-outline-secondary">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Santri</th>
                                <th>Jenis Kelamin</th>
                                <th>Wali / Penanggung Jawab</th>
                                <th>Tanggal Masuk</th>
                                <th>Status</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($santris as $managedSantri)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            @if ($managedSantri->photoUrl())
                                                <img src="{{ $managedSantri->photoUrl() }}" alt="Foto {{ $managedSantri->full_name }}" class="user-inline-avatar">
                                            @else
                                                <span class="user-detail-avatar" style="width: 3rem; height: 3rem; border-radius: 999px; font-size: 1rem;">
                                                    {{ strtoupper(substr($managedSantri->full_name, 0, 1)) }}
                                                </span>
                                            @endif

                                            <div>
                                                <div class="fw-semibold">{{ $managedSantri->full_name }}</div>
                                                <div class="text-secondary small mt-1">NIS: {{ $managedSantri->nis }}</div>
                                                <div class="text-secondary small mt-1">
                                                    {{ $managedSantri->birth_place }}, {{ optional($managedSantri->birth_date)->translatedFormat('d M Y') }}
                                                </div>
                                                <div class="text-secondary small mt-1">
                                                    {{ $managedSantri->room_name ?: 'Kamar belum diatur' }} • Angkatan {{ $managedSantri->entry_year ?? '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $genderBadgeClasses[$managedSantri->gender] ?? 'bg-secondary-lt text-secondary' }}">
                                            {{ $managedSantri->genderLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $managedSantri->guardian_name ?: '-' }}</div>
                                        <div class="text-secondary small mt-1">{{ $managedSantri->guardian_phone_number ?: 'Belum diisi' }}</div>
                                    </td>
                                    <td>
                                        <div>{{ optional($managedSantri->entry_date)->translatedFormat('d M Y') }}</div>
                                        <div class="text-secondary small mt-1">Input oleh {{ $managedSantri->creator?->name ?? 'System' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusBadgeClasses[$managedSantri->status] ?? 'bg-secondary-lt text-secondary' }}">
                                            {{ $managedSantri->statusLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary dropdown-toggle user-action-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Action
                                            </button>

                                            <div class="dropdown-menu dropdown-menu-end p-3 user-action-menu">
                                                <a href="{{ route('santri.show', $managedSantri) }}" class="btn btn-outline-secondary btn-sm w-100">
                                                    Lihat Detail Santri
                                                </a>

                                                @can('update', $managedSantri)
                                                    <div class="dropdown-divider my-3"></div>

                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-primary btn-sm w-100"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editSantriModal{{ $managedSantri->id }}"
                                                    >
                                                        Edit Data Santri
                                                    </button>
                                                @endcan

                                                @can('delete', $managedSantri)
                                                    <div class="dropdown-divider my-3"></div>

                                                    <form method="POST" action="{{ route('santri.destroy', $managedSantri) }}" onsubmit="return confirm('Hapus permanen data santri ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                                            Hapus Santri
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </div>

                                        @can('update', $managedSantri)
                                            <div class="modal modal-blur fade" id="editSantriModal{{ $managedSantri->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('santri.update', $managedSantri) }}" enctype="multipart/form-data">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="editing_santri_id" value="{{ $managedSantri->id }}">

                                                            <div class="modal-header">
                                                                <div>
                                                                    <h5 class="modal-title">Edit Santri</h5>
                                                                    <div class="text-secondary small mt-1">Perbarui identitas, wali, status, dan foto santri.</div>
                                                                </div>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>

                                                            <div class="modal-body">
                                                                @include('santri.partials.form-fields', [
                                                                    'santriFormId' => 'edit_'.$managedSantri->id,
                                                                    'santriItem' => $managedSantri,
                                                                    'genders' => $genders,
                                                                    'statuses' => $statuses,
                                                                    'errorsBag' => $errors->updateSantri,
                                                                ])
                                                            </div>

                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-secondary">Belum ada data santri yang tersimpan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($santris->hasPages())
                    <div class="card-footer">
                        {{ $santris->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($canCreateSantri)
        <div class="modal modal-blur fade" id="createSantriModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('santri.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Tambah Santri Baru</h5>
                                <div class="text-secondary small mt-1">Lengkapi data identitas, orang tua, kamar, angkatan, tanggal masuk, dan status awal santri.</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            @include('santri.partials.form-fields', [
                                'santriFormId' => 'create',
                                'santriItem' => null,
                                'genders' => $genders,
                                'statuses' => $statuses,
                                'errorsBag' => $errors->createSantri,
                            ])
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if ($errors->createSantri->any())
                document.getElementById('open-create-santri-modal')?.click();
            @endif

            @if ($errors->updateSantri->any() && old('editing_santri_id'))
                const editModalElement = document.getElementById('editSantriModal{{ old('editing_santri_id') }}');

                if (editModalElement && window.bootstrap?.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(editModalElement).show();
                }
            @endif
        });
    </script>
</x-app-layout>
