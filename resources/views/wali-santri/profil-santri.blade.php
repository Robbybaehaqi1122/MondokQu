<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Profil Santri</h2>
                <div class="text-secondary mt-1">{{ $santri->full_name }}</div>
            </div>
            <a href="{{ route('wali-santri.dashboard') }}" class="btn btn-outline-primary">
                <i class="ti ti-arrow-left me-1"></i>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <span class="avatar avatar-xl mb-3 {{ $santri->gender === 'male' ? 'bg-blue-lt text-blue' : 'bg-pink-lt text-pink' }}">
                        <i class="ti ti-user fs-1"></i>
                    </span>
                    <h3 class="card-title mb-1">{{ $santri->full_name }}</h3>
                    <div class="text-secondary small">NIS {{ $santri->nis }}</div>
                    <span class="badge mt-2 {{ $santri->status === 'active' ? 'bg-success-lt text-success' : ($santri->status === 'leave' ? 'bg-warning-lt text-warning' : 'bg-secondary-lt text-secondary') }}">
                        {{ $santri->statusLabel() }}
                    </span>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Navigasi</h3>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('wali-santri.absensi', $santri) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                        <span class="avatar avatar-sm bg-azure-lt text-azure">
                            <i class="ti ti-clipboard-check"></i>
                        </span>
                        <span>Riwayat Absensi</span>
                    </a>
                    <a href="{{ route('wali-santri.pelanggaran', $santri) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                        <span class="avatar avatar-sm bg-danger-lt text-danger">
                            <i class="ti ti-alert-triangle"></i>
                        </span>
                        <span>Riwayat Pelanggaran</span>
                    </a>
                    <a href="{{ route('wali-santri.tahfidz', $santri) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                        <span class="avatar avatar-sm bg-green-lt text-green">
                            <i class="ti ti-book"></i>
                        </span>
                        <span>Riwayat Tahfidz</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Data Diri</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-secondary small">Nama Lengkap</div>
                            <div class="fw-semibold mt-1">{{ $santri->full_name }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small">NIS</div>
                            <div class="fw-semibold mt-1">{{ $santri->nis ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small">Jenis Kelamin</div>
                            <div class="fw-semibold mt-1">{{ $santri->genderLabel() }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small">Status</div>
                            <div class="fw-semibold mt-1">{{ $santri->statusLabel() }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small">Tempat Lahir</div>
                            <div class="fw-semibold mt-1">{{ $santri->birth_place ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small">Tanggal Lahir</div>
                            <div class="fw-semibold mt-1">{{ $santri->birth_date?->translatedFormat('d M Y') ?: '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-secondary small">Alamat</div>
                            <div class="fw-semibold mt-1">{{ $santri->address ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small">Kamar</div>
                            <div class="fw-semibold mt-1">{{ $santri->displayRoomName('Belum diatur') }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small">Angkatan</div>
                            <div class="fw-semibold mt-1">{{ $santri->entry_year ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small">Tanggal Masuk</div>
                            <div class="fw-semibold mt-1">{{ $santri->entry_date?->translatedFormat('d M Y') ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Data Ayah</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-secondary small">Nama</div>
                            <div class="fw-semibold mt-1">{{ $santri->father_name ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small">No. HP</div>
                            <div class="fw-semibold mt-1">{{ $santri->father_phone ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small">Pendidikan</div>
                            <div class="fw-semibold mt-1">{{ $santri->father_education ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small">Pekerjaan</div>
                            <div class="fw-semibold mt-1">{{ $santri->father_job ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Data Ibu</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-secondary small">Nama</div>
                            <div class="fw-semibold mt-1">{{ $santri->mother_name ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small">No. HP</div>
                            <div class="fw-semibold mt-1">{{ $santri->mother_phone ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small">Pendidikan</div>
                            <div class="fw-semibold mt-1">{{ $santri->mother_education ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small">Pekerjaan</div>
                            <div class="fw-semibold mt-1">{{ $santri->mother_job ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Data Wali</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-secondary small">Nama</div>
                            <div class="fw-semibold mt-1">{{ $santri->displayGuardianName() }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small">No. HP</div>
                            <div class="fw-semibold mt-1">{{ $santri->displayGuardianPhone() }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small">Hubungan</div>
                            <div class="fw-semibold mt-1">{{ $santri->guardian_relation ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-secondary small">Kontak Darurat</div>
                            <div class="fw-semibold mt-1">{{ $santri->emergency_contact ?: '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-secondary small">Alamat</div>
                            <div class="fw-semibold mt-1">{{ $santri->guardian_address ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($santri->notes)
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Catatan</h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $santri->notes }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
