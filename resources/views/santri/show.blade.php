<x-app-layout>
    @php
        $statusBadgeClasses = [
            'active' => 'bg-success-lt text-success',
            'leave' => 'bg-warning-lt text-warning',
            'exited' => 'bg-warning-lt text-warning',
            'alumni' => 'bg-azure-lt text-azure',
        ];

        $santriPhotoSizeClass = 'santri-detail-avatar-frame';
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="page-title">Detail Santri</h2>
            <div class="text-secondary mt-1">Lihat ringkasan identitas, wali, dan status santri.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="card user-detail-hero-card">
                <div class="card-body">
                    <div class="row g-4 align-items-start">
                        <div class="col-lg-8">
                            <div class="d-flex flex-column flex-md-row gap-4 align-items-md-start">
                                <div class="{{ $santriPhotoSizeClass }}">
                                    @if ($santri->photoUrl())
                                        <img
                                            src="{{ $santri->photoUrl() }}"
                                            alt="Foto {{ $santri->full_name }}"
                                            class="user-detail-avatar-image"
                                            onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');"
                                        >
                                        <div class="user-detail-avatar d-none">
                                            {{ strtoupper(substr($santri->full_name, 0, 1)) }}
                                        </div>
                                    @else
                                        <div class="user-detail-avatar">
                                            {{ strtoupper(substr($santri->full_name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-fill">
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <span class="badge {{ $statusBadgeClasses[$santri->status] ?? 'bg-secondary-lt text-secondary' }}">
                                            {{ $santri->statusLabel() }}
                                        </span>
                                        <span class="badge bg-blue-lt text-blue">{{ $santri->genderLabel() }}</span>
                                    </div>

                                    <div class="text-secondary text-uppercase small fw-bold">Santri</div>
                                    <h3 class="mb-1">{{ $santri->full_name }}</h3>
                                    <div class="text-secondary mb-3 user-detail-subtitle">
                                        <span>NIS {{ $santri->nis }}</span>
                                        <span class="user-detail-separator"></span>
                                        <span>{{ $santri->birth_place }}, {{ optional($santri->birth_date)->translatedFormat('d M Y') }}</span>
                                    </div>

                                    <div class="user-detail-meta-grid">
                                        <div class="user-detail-meta-card">
                                            <div class="text-secondary small text-uppercase fw-bold">Jenis Kelamin</div>
                                            <div class="fw-semibold mt-2">{{ $santri->genderLabel() }}</div>
                                        </div>
                                        <div class="user-detail-meta-card">
                                            <div class="text-secondary small text-uppercase fw-bold">Status</div>
                                            <div class="fw-semibold mt-2">{{ $santri->statusLabel() }}</div>
                                        </div>
                                        <div class="user-detail-meta-card">
                                            <div class="text-secondary small text-uppercase fw-bold">Tanggal Masuk</div>
                                            <div class="fw-semibold mt-2">{{ optional($santri->entry_date)->translatedFormat('d M Y') }}</div>
                                        </div>
                                        <div class="user-detail-meta-card">
                                            <div class="text-secondary small text-uppercase fw-bold">Angkatan</div>
                                            <div class="fw-semibold mt-2">{{ $santri->entry_year ?? '-' }}</div>
                                        </div>
                                        <div class="user-detail-meta-card">
                                            <div class="text-secondary small text-uppercase fw-bold">Kamar / Asrama</div>
                                            <div class="fw-semibold mt-2">{{ $santri->room_name ?: '-' }}</div>
                                        </div>
                                        <div class="user-detail-meta-card">
                                            <div class="text-secondary small text-uppercase fw-bold">Wali / Penanggung Jawab</div>
                                            <div class="fw-semibold mt-2">{{ $santri->guardian_name ?: '-' }}</div>
                                        </div>
                                        <div class="user-detail-meta-card">
                                            <div class="text-secondary small text-uppercase fw-bold">No. HP Wali / Penanggung Jawab</div>
                                            <div class="fw-semibold mt-2">{{ $santri->guardian_phone_number ?: '-' }}</div>
                                        </div>
                                        <div class="user-detail-meta-card">
                                            <div class="text-secondary small text-uppercase fw-bold">Diinput Oleh</div>
                                            <div class="fw-semibold mt-2">{{ $santri->creator?->name ?? 'System' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="user-detail-actions">
                                <div class="user-detail-actions-head">
                                    <div class="text-secondary small text-uppercase fw-bold">Status Saat Ini</div>
                                    <h3 class="card-title mt-2 mb-1">Kontrol Santri</h3>
                                </div>

                                <div class="user-detail-action-block">
                                    <div class="mt-1">
                                        <span class="badge {{ $statusBadgeClasses[$santri->status] ?? 'bg-secondary-lt text-secondary' }}">
                                            {{ $santri->statusLabel() }}
                                        </span>
                                    </div>
                                    <div class="text-secondary small mt-3">
                                        Tanggal masuk: {{ optional($santri->entry_date)->translatedFormat('d M Y') }}
                                    </div>
                                </div>

                                <a href="{{ route('santri.index') }}" class="btn btn-outline-secondary">Kembali ke Daftar Santri</a>

                                @if ($canDeleteSantri)
                                    <form method="POST" action="{{ route('santri.destroy', $santri) }}" onsubmit="return confirm('Hapus permanen data santri ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger w-100">Hapus Santri</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Identitas Santri</h3>
                </div>
                <div class="card-body user-detail-info-list">
                    <div class="user-detail-info-row">
                        <span>NIS</span>
                        <strong class="user-detail-info-value">{{ $santri->nis }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Nama Lengkap</span>
                        <strong class="user-detail-info-value">{{ $santri->full_name }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Jenis Kelamin</span>
                        <strong class="user-detail-info-value">{{ $santri->genderLabel() }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Tempat, Tanggal Lahir</span>
                        <strong class="user-detail-info-value">{{ $santri->birth_place }}, {{ optional($santri->birth_date)->translatedFormat('d M Y') }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Alamat</span>
                        <strong class="user-detail-info-value">{{ $santri->address }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Kamar / Asrama</span>
                        <strong class="user-detail-info-value">{{ $santri->room_name ?: '-' }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Angkatan</span>
                        <strong class="user-detail-info-value">{{ $santri->entry_year ?? '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Orang Tua, Wali & Administrasi</h3>
                </div>
                <div class="card-body user-detail-info-list">
                    <div class="user-detail-info-row">
                        <span>Wali / Penanggung Jawab</span>
                        <strong class="user-detail-info-value">{{ $santri->guardian_name ?: '-' }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Nama Ayah</span>
                        <strong class="user-detail-info-value">{{ $santri->father_name ?: '-' }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Nama Ibu</span>
                        <strong class="user-detail-info-value">{{ $santri->mother_name ?: '-' }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>No. HP Wali / Penanggung Jawab</span>
                        <strong class="user-detail-info-value">{{ $santri->guardian_phone_number ?: '-' }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Kontak Darurat</span>
                        <strong class="user-detail-info-value">{{ $santri->emergency_contact ?: '-' }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Tanggal Masuk</span>
                        <strong class="user-detail-info-value">{{ optional($santri->entry_date)->translatedFormat('d M Y') }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Angkatan</span>
                        <strong class="user-detail-info-value">{{ $santri->entry_year ?? '-' }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Status</span>
                        <strong class="user-detail-info-value">{{ $santri->statusLabel() }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Diinput Oleh</span>
                        <strong class="user-detail-info-value">{{ $santri->creator?->name ?? 'System' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Catatan Singkat</h3>
                </div>
                <div class="card-body">
                    <div class="text-secondary">
                        {{ $santri->notes ?: 'Belum ada catatan singkat untuk santri ini.' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
