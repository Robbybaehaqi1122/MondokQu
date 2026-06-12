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
                                            loading="lazy"
                                            class="user-detail-avatar-image"
                                            data-error-fallback="true"
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
                                            <div class="fw-semibold mt-2">{{ $santri->displayRoomName() }}</div>
                                        </div>
                                        <div class="user-detail-meta-card">
                                            <div class="text-secondary small text-uppercase fw-bold">Wali / Penanggung Jawab</div>
                                            <div class="fw-semibold mt-2">{{ $santri->displayGuardianName() }}</div>
                                        </div>
                                        <div class="user-detail-meta-card">
                                            <div class="text-secondary small text-uppercase fw-bold">No. HP Wali / Penanggung Jawab</div>
                                            <div class="fw-semibold mt-2">{{ $santri->displayGuardianPhone() }}</div>
                                        </div>
                                        <div class="user-detail-meta-card">
                                            <div class="text-secondary small text-uppercase fw-bold">Akun Wali Portal</div>
                                            <div class="fw-semibold mt-2">
                                                @forelse ($santri->guardians as $guardianUser)
                                                    <span class="badge bg-indigo-lt text-indigo me-1 mb-1">{{ $guardianUser->name }}</span>
                                                @empty
                                                    -
                                                @endforelse
                                            </div>
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
                        <strong class="user-detail-info-value">{{ $santri->displayRoomName() }}</strong>
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
                        <strong class="user-detail-info-value">{{ $santri->displayGuardianName() }}</strong>
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
                        <strong class="user-detail-info-value">{{ $santri->displayGuardianPhone() }}</strong>
                    </div>
                    <div class="user-detail-info-row">
                        <span>Akun Wali Portal</span>
                        <strong class="user-detail-info-value">
                            @forelse ($santri->guardians as $guardianUser)
                                <span class="badge bg-indigo-lt text-indigo me-1 mb-1">{{ $guardianUser->name }}</span>
                            @empty
                                -
                            @endforelse
                        </strong>
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

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-wrap align-items-center gap-3 w-100">
                        <div>
                            <h3 class="card-title">Dokumen Santri</h3>
                            @php $missingDocs = $santri->missingDocumentTypes(); @endphp
                            @if ($santri->isDocumentComplete())
                                <span class="badge bg-success-lt text-success mt-1">Dokumen Lengkap</span>
                            @else
                                <span class="badge bg-warning-lt text-warning mt-1">Kurang: {{ collect($missingDocs)->map(fn($t) => \App\Models\SantriDocument::types()[$t] ?? $t)->implode(', ') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if ($canUpdateSantri)
                        <form id="upload-doc-form" method="POST" action="{{ route('santri.documents.upload', $santri) }}" enctype="multipart/form-data" class="row g-3 align-items-end mb-4 p-3 bg-secondary-lt rounded">
                            @csrf
                            <div class="col-md-3">
                                <label for="doc_type" class="form-label">Jenis Dokumen</label>
                                <select id="doc_type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">-- Pilih --</option>
                                    @foreach (\App\Models\SantriDocument::types() as $type => $label)
                                        <option value="{{ $type }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="doc_file" class="form-label">File (PDF/JPG/PNG, max 10MB)</label>
                                <input id="doc_file" name="file" type="file" class="form-control @error('file') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="doc_notes" class="form-label">Catatan (opsional)</label>
                                <input id="doc_notes" name="notes" type="text" class="form-control" maxlength="500" placeholder="Mis: scan halaman 1-2">
                            </div>
                            <div class="col-md-2 d-grid">
                                <button type="submit" class="btn btn-primary" id="upload-doc-btn">
                                    <span id="upload-doc-icon"><i class="ti ti-upload me-1"></i></span>
                                    <span id="upload-doc-spinner" class="spinner-border spinner-border-sm me-1" role="status" hidden></span>
                                    <span id="upload-doc-text">Upload</span>
                                </button>
                            </div>
                        </form>
                    @endif

                    @if ($santri->documents->isEmpty())
                        <div class="text-secondary">Belum ada dokumen yang diupload.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Jenis Dokumen</th>
                                        <th>Nama File</th>
                                        <th>Ukuran</th>
                                        <th>Catatan</th>
                                        <th>Diupload</th>
                                        <th class="w-1">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($santri->documents as $doc)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary-lt text-primary">{{ $doc->typeLabel() }}</span>
                                            </td>
                                            <td>
                                                <span class="text-break">{{ $doc->original_name }}</span>
                                            </td>
                                            <td class="text-nowrap">{{ $doc->fileSizeForHumans() }}</td>
                                            <td>{{ $doc->notes ?: '-' }}</td>
                                            <td class="text-nowrap">
                                                <div>{{ $doc->created_at->translatedFormat('d M Y') }}</div>
                                                <div class="text-secondary small">{{ $doc->uploader?->name }}</div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn btn-outline-secondary btn-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ti ti-dots-vertical"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a href="{{ route('santri.documents.download', [$santri, $doc]) }}" class="dropdown-item">
                                                            <i class="ti ti-download me-2"></i> Download
                                                        </a>
                                                        @if ($canUpdateSantri)
                                                            <form method="POST" action="{{ route('santri.documents.destroy', [$santri, $doc]) }}" onsubmit="return confirm('Hapus dokumen ini?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="ti ti-trash me-2"></i> Hapus
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
<script>
    document.getElementById('upload-doc-form')?.addEventListener('submit', function() {
        setTimeout(() => {
            const btn = document.getElementById('upload-doc-btn');
            if (btn) {
                btn.disabled = true;
                document.getElementById('upload-doc-icon').hidden = true;
                document.getElementById('upload-doc-spinner').hidden = false;
                document.getElementById('upload-doc-text').textContent = 'Mengupload...';
            }
        }, 50);
    });
</script>
</x-app-layout>
