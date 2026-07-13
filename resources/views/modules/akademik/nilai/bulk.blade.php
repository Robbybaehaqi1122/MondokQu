<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Bulk Input Nilai</h2>
            </div>
            <a href="{{ route('akademik.nilai.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3" id="filter-form">
                <div class="col-md-3">
                    <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                    <select name="mata_pelajaran_id" id="mata_pelajaran_id" class="form-select" required>
                        <option value="">-- Pilih Mapel --</option>
                        @foreach ($mapels as $mapel)
                            <option value="{{ $mapel->id }}"
                                data-grade-level-ids="{{ $mapel->gradeLevels->pluck('id')->join(',') }}"
                                {{ request('mata_pelajaran_id') == $mapel->id ? 'selected' : '' }}>
                                {{ $mapel->nama }} (KKM: {{ $mapel->kkm }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Semester <span class="text-danger">*</span></label>
                    <select name="semester" id="semester" class="form-select" required>
                        <option value="">-- Pilih Semester --</option>
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem }}" {{ request('semester') == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ruangan <span class="text-danger">*</span></label>
                    <select name="room_id" id="room_id" class="form-select" required>
                        <option value="">-- Pilih Ruangan --</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}"
                                data-grade-level-id="{{ $room->grade_level_id }}"
                                {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                {{ $room->name }} ({{ $room->gradeLevel?->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" id="load-btn" class="btn btn-primary w-100" disabled>
                        <i class="ti ti-download me-1"></i> Muat Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    <form id="bulk-form" action="{{ route('akademik.nilai.bulk-store') }}" method="POST" style="display:none;">
        @csrf
        <input type="hidden" name="mata_pelajaran_id" id="form_mata_pelajaran_id">
        <input type="hidden" name="semester" id="form_semester">
        <input type="hidden" name="room_id" id="form_room_id">

        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="card-title">Daftar Santri</h3>
                    <span id="student-count" class="badge bg-primary-lt text-primary"></span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table" id="grades-table">
                    <thead>
                        <tr>
                            <th style="width:40px">No</th>
                            <th>Santri</th>
                            <th style="width:120px">Pengetahuan</th>
                            <th style="width:120px">Keterampilan</th>
                            <th style="width:80px">Akhir</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody id="grades-body">
                        <tr>
                            <td colspan="6" class="text-secondary text-center py-4" id="empty-state">
                                Pilih mata pelajaran, semester, dan ruangan, lalu klik "Muat Data".
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" onclick="fillAll(80)">
                    <i class="ti ti-pencil me-1"></i> Isi Semua (80)
                </button>
                <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                    <i class="ti ti-device-floppy me-1"></i> Simpan Semua
                </button>
            </div>
        </div>
    </form>
</x-app-layout>

@push('scripts')
<script>
    const mapelSelect = document.getElementById('mata_pelajaran_id');
    const semesterSelect = document.getElementById('semester');
    const roomSelect = document.getElementById('room_id');
    const loadBtn = document.getElementById('load-btn');
    const bulkForm = document.getElementById('bulk-form');
    const gradesBody = document.getElementById('grades-body');
    const submitBtn = document.getElementById('submit-btn');
    const studentCount = document.getElementById('student-count');

    function checkReady() {
        loadBtn.disabled = !(mapelSelect.value && semesterSelect.value && roomSelect.value);
    }

    mapelSelect.addEventListener('change', checkReady);
    semesterSelect.addEventListener('change', checkReady);
    roomSelect.addEventListener('change', checkReady);

    loadBtn.addEventListener('click', async () => {
        loadBtn.disabled = true;
        loadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memuat...';
        gradesBody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span> Memuat data santri...</td></tr>';

        try {
            const params = new URLSearchParams({
                mata_pelajaran_id: mapelSelect.value,
                semester: semesterSelect.value,
                room_id: roomSelect.value,
            });

            const resp = await fetch(`{{ route('akademik.nilai.bulk-students') }}?${params}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await resp.json();

            if (data.santris.length === 0) {
                gradesBody.innerHTML = '<tr><td colspan="6" class="text-secondary text-center py-4">Tidak ada santri aktif di ruangan ini.</td></tr>';
                submitBtn.disabled = true;
                studentCount.textContent = '0 santri';
                return;
            }

            document.getElementById('form_mata_pelajaran_id').value = mapelSelect.value;
            document.getElementById('form_semester').value = semesterSelect.value;
            document.getElementById('form_room_id').value = roomSelect.value;

            let html = '';
            data.santris.forEach((s, i) => {
                const akhir = Math.round(((parseInt(s.nilai_pengetahuan) || 0) + (parseInt(s.nilai_keterampilan) || 0)) / 2) || '-';
                html += `
                    <tr data-index="${i}">
                        <td class="text-secondary">${i + 1}</td>
                        <td>
                            <div class="fw-semibold">${s.full_name}</div>
                            <div class="text-secondary small">${s.nis}</div>
                            <input type="hidden" name="grades[${i}][santri_id]" value="${s.santri_id}">
                        </td>
                        <td>
                            <input type="number" name="grades[${i}][nilai_pengetahuan]"
                                class="form-control pengetahuan-input"
                                value="${s.nilai_pengetahuan}" min="0" max="100" required>
                        </td>
                        <td>
                            <input type="number" name="grades[${i}][nilai_keterampilan]"
                                class="form-control keterampilan-input"
                                value="${s.nilai_keterampilan}" min="0" max="100" required>
                        </td>
                        <td class="fw-bold akhir-cell">${akhir}</td>
                        <td>
                            <input type="text" name="grades[${i}][notes]"
                                class="form-control form-control-sm" value="${s.notes || ''}" placeholder="Catatan">
                        </td>
                    </tr>`;
            });

            gradesBody.innerHTML = html;
            studentCount.textContent = `${data.santris.length} santri`;
            submitBtn.disabled = false;

            gradesBody.querySelectorAll('tr').forEach(row => {
                const pen = row.querySelector('.pengetahuan-input');
                const ket = row.querySelector('.keterampilan-input');
                const akhirCell = row.querySelector('.akhir-cell');
                if (!pen || !ket) return;

                const updateAkhir = () => {
                    const p = parseInt(pen.value) || 0;
                    const k = parseInt(ket.value) || 0;
                    akhirCell.textContent = (pen.value && ket.value) ? Math.round((p + k) / 2) : '-';
                };
                pen.addEventListener('input', updateAkhir);
                ket.addEventListener('input', updateAkhir);
            });
        } catch (e) {
            gradesBody.innerHTML = '<tr><td colspan="6" class="text-danger text-center py-4">Gagal memuat data. Coba lagi.</td></tr>';
        } finally {
            loadBtn.disabled = false;
            loadBtn.innerHTML = '<i class="ti ti-download me-1"></i> Muat Data';
        }
    });

    bulkForm.addEventListener('submit', (e) => {
        if (!confirm('Simpan semua nilai yang sudah diisi?')) {
            e.preventDefault();
        }
    });
</script>
@endpush
