<x-app-layout>
    <div class="d-flex align-items-center gap-3 mb-3">
        <a href="{{ route('attendance.dashboard') }}" class="btn btn-ghost-secondary">
            <i class="ti ti-arrow-left"></i>
            Kembali
        </a>
        <div>
            <h2 class="page-title mb-1">Scan Barcode Santri</h2>
            <div class="text-secondary">Scan kartu barcode atau cari nama santri untuk absen</div>
        </div>
    </div>
<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Scanner Kamera</h3>
                <div class="card-actions">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-start-camera" onclick="startCamera()">
                        <i class="ti ti-camera"></i>
                        Mulai Kamera
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm d-none" id="btn-stop-camera" onclick="stopCamera()">
                        <i class="ti ti-camera-off"></i>
                        Hentikan Kamera
                    </button>
                </div>
            </div>
            <div class="card-body text-center">
                <div id="qr-reader" style="width:100%;max-width:400px;margin:0 auto;display:none;"></div>
                <div id="qr-placeholder" class="py-5 text-secondary">
                    <i class="ti ti-scan" style="font-size:4rem;"></i>
                    <p class="mt-3 mb-0">Tekan <strong>Mulai Kamera</strong> untuk memindai barcode</p>
                </div>
                <div id="scan-result" class="mt-3"></div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Input Manual</h3>
            </div>
            <div class="card-body">
                <form id="form-manual" onsubmit="return cariBarcode()">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="ti ti-barcode"></i>
                        </span>
                        <input type="text" id="input-barcode" class="form-control" placeholder="Ketik atau scan barcode santri..." maxlength="20" autocomplete="off">
                        <button type="submit" class="btn btn-primary">Cari</button>
                    </div>
                </form>
                <hr class="my-3">
                <div class="mb-2 text-secondary small">Atau cari berdasarkan nama / NIS:</div>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="ti ti-search"></i>
                    </span>
                    <input type="text" id="input-nama" class="form-control" placeholder="Nama atau NIS santri..." minlength="2" autocomplete="off">
                </div>
                <div id="search-results" class="mt-2"></div>
            </div>
        </div>

        <div class="card mt-3 d-none" id="session-section">
            <div class="card-header">
                <h3 class="card-title">Pilih Sesi Absensi</h3>
            </div>
            <div class="card-body" id="session-list">
                @forelse ($todaySessions as $session)
                    <div class="form-check">
                        <input class="form-check-input session-radio" type="radio" name="session_id" value="{{ $session->id }}" id="session-{{ $session->id }}" @if ($loop->first) checked @endif>
                        <label class="form-check-label" for="session-{{ $session->id }}">
                            {{ $session->activity->name }}
                            <span class="text-secondary small">({{ \Carbon\Carbon::parse($session->activity->start_time)->format('H:i') }}{{ $session->activity->end_time ? ' - ' . \Carbon\Carbon::parse($session->activity->end_time)->format('H:i') : '' }})</span>
                            <span class="badge bg-{{ $session->status === 'open' ? 'success' : 'warning' }} ms-1">{{ $session->statusLabel() }}</span>
                        </label>
                    </div>
                @empty
                    <p class="text-secondary mb-0">Tidak ada sesi absensi untuk hari ini.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5" id="santri-panel">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Data Santri</h3>
            </div>
            <div class="card-body text-center py-5" id="santri-empty">
                <i class="ti ti-user-search" style="font-size:4rem;opacity:0.3;"></i>
                <p class="mt-3 text-secondary">Scan barcode atau cari nama santri untuk memulai absen</p>
            </div>
            <div class="card-body d-none" id="santri-info">
                <div class="text-center mb-3">
                    <div id="santri-photo" class="mb-2">
                        <div class="avatar avatar-xl" id="santri-avatar" style="background:var(--tblr-primary);color:#fff;font-size:2rem;width:96px;height:96px;margin:0 auto;"></div>
                    </div>
                    <h3 class="mb-1" id="santri-name"></h3>
                    <div class="text-secondary" id="santri-detail"></div>
                </div>
                <hr>
                <form id="form-absen" method="POST" action="{{ route('attendance.scan.record') }}">
                    @csrf
                    <input type="hidden" name="santri_id" id="santri-id">
                    <input type="hidden" name="session_id" id="session-id">
                    <div class="mb-3">
                        <label class="form-label">Status Absen</label>
                        <div class="row g-2" id="status-options">
                            @foreach ($statusOptions as $opt)
                                <div class="col-6 col-sm-4">
                                    <label class="btn btn-outline-secondary w-100 p-2 text-center status-btn @if ($opt['value'] === 'present') active border-primary text-primary @endif" data-value="{{ $opt['value'] }}">
                                        <div class="small fw-semibold">
                                            @switch($opt['value'])
                                                @case('present') <i class="ti ti-circle-check text-success"></i> @break
                                                @case('permission') <i class="ti ti-file-text text-warning"></i> @break
                                                @case('sick') <i class="ti ti-heartbeat text-danger"></i> @break
                                                @case('absent') <i class="ti ti-circle-x text-danger"></i> @break
                                                @case('late') <i class="ti ti-clock text-orange"></i> @break
                                            @endswitch
                                            {{ $opt['label'] }}
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <input type="hidden" name="status" id="status-value" value="present">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan <span class="text-secondary">(opsional)</span></label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="btn-submit">
                        <i class="ti ti-device-floppy"></i>
                        Simpan Absen
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let html5QrCode = null;
let isScanning = false;
let selectedStatus = 'present';

document.querySelectorAll('.status-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.status-btn').forEach(function(b) {
            b.classList.remove('active', 'border-primary', 'text-primary');
        });
        this.classList.add('active', 'border-primary', 'text-primary');
        selectedStatus = this.dataset.value;
        document.getElementById('status-value').value = selectedStatus;
    });
});

function startCamera() {
    if (isScanning) return;

    var readerEl = document.getElementById('qr-reader');
    readerEl.style.display = 'block';
    document.getElementById('qr-placeholder').style.display = 'none';
    document.getElementById('btn-start-camera').classList.add('d-none');
    document.getElementById('btn-stop-camera').classList.remove('d-none');

    html5QrCode = new Html5Qrcode('qr-reader');

    html5QrCode.start(
        { facingMode: 'environment' },
        {
            fps: 10,
            qrbox: { width: 250, height: 250 },
        },
        function(qrCodeMessage) {
            stopCamera();
            document.getElementById('input-barcode').value = qrCodeMessage;
            cariBarcode();
        }
    ).then(function() {
        isScanning = true;
    }).catch(function(err) {
        console.error('Camera error:', err);
        alert('Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.');
        stopCamera();
    });
}

function stopCamera() {
    if (html5QrCode) {
        try {
            html5QrCode.stop();
            html5QrCode.clear();
        } catch(e) {}
        html5QrCode = null;
    }
    isScanning = false;
    document.getElementById('qr-reader').style.display = 'none';
    document.getElementById('qr-placeholder').style.display = 'block';
    document.getElementById('btn-start-camera').classList.remove('d-none');
    document.getElementById('btn-stop-camera').classList.add('d-none');
}

function cariBarcode() {
    var barcode = document.getElementById('input-barcode').value.trim();
    if (!barcode) return false;

    document.getElementById('scan-result').innerHTML = '<div class="spinner-border spinner-border-sm text-primary"></div> Mencari...';

    fetch('{{ route("attendance.scan.search") }}?barcode=' + encodeURIComponent(barcode))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.found) {
                tampilkanSantri(data.santri);
                document.getElementById('scan-result').innerHTML = '<div class="alert alert-success py-2 mb-0"><i class="ti ti-check"></i> Santri ditemukan!</div>';
            } else {
                document.getElementById('scan-result').innerHTML = '<div class="alert alert-danger py-2 mb-0"><i class="ti ti-alert-triangle"></i> ' + data.message + '</div>';
                sembunyikanPanel();
            }
        })
        .catch(function() {
            document.getElementById('scan-result').innerHTML = '<div class="alert alert-danger py-2 mb-0">Gagal menghubungi server.</div>';
        });

    return false;
}

var searchTimer = null;
document.getElementById('input-nama').addEventListener('input', function() {
    clearTimeout(searchTimer);
    var q = this.value.trim();
    if (q.length < 2) {
        document.getElementById('search-results').innerHTML = '';
        return;
    }
                searchTimer = setTimeout(function() {
                    fetch('{{ route("attendance.scan.search-name") }}?q=' + encodeURIComponent(q))
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            var html = '';
                            if (data.santris.length === 0) {
                                html = '<div class="text-secondary small mt-2">Tidak ada santri ditemukan.</div>';
                            } else {
                                html = '<div class="list-group mt-2" style="max-height:250px;overflow-y:auto;">';
                                data.santris.forEach(function(s) {
                                    var initial = s.full_name.charAt(0).toUpperCase();
                                    html += '<a href="#" class="list-group-item list-group-item-action d-flex align-items-center gap-3" data-santri=\'' + JSON.stringify(s).replace(/'/g, "&#39;") + '\'>';
                                    html += '<span class="avatar avatar-sm" style="background:var(--tblr-primary);color:#fff;">' + initial + '</span>';
                                    html += '<div><div class="fw-semibold">' + s.full_name + '</div><div class="text-secondary small">' + (s.nis ? 'NIS: ' + s.nis : '') + (s.room ? ' &middot; ' + s.room : '') + '</div></div>';
                                    html += '</a>';
                                });
                                html += '</div>';
                            }
                            document.getElementById('search-results').innerHTML = html;
                            document.querySelectorAll('[data-santri]').forEach(function(el) {
                                el.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    tampilkanSantri(JSON.parse(this.dataset.santri));
                                });
                            });
                        });
                }, 300);
});

function tampilkanSantri(data) {
    if (typeof data === 'string') {
        data = JSON.parse(data);
    }

    document.getElementById('santri-empty').classList.add('d-none');
    var info = document.getElementById('santri-info');
    info.classList.remove('d-none');

    document.getElementById('santri-id').value = data.id;

    var photoUrl = data.photo_url;
    var avatarEl = document.getElementById('santri-avatar');
    if (photoUrl) {
        avatarEl.style.background = 'transparent';
        avatarEl.innerHTML = '<img src="' + photoUrl + '" alt="' + data.full_name + '" style="width:96px;height:96px;border-radius:50%;object-fit:cover;">';
    } else {
        avatarEl.style.background = 'var(--tblr-primary)';
        avatarEl.innerHTML = data.full_name.charAt(0).toUpperCase();
    }

    document.getElementById('santri-name').textContent = data.full_name;
    document.getElementById('santri-detail').innerHTML = (data.nis ? 'NIS: ' + data.nis + '<br>' : '') + (data.room ? 'Kamar: ' + data.room : '') + (data.gender_label ? '<br>' + data.gender_label : '');

    var sessionRadio = document.querySelector('.session-radio:checked');
    if (sessionRadio) {
        document.getElementById('session-id').value = sessionRadio.value;
    }

    document.getElementById('session-section').classList.remove('d-none');

    document.getElementById('form-absen').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function sembunyikanPanel() {
    document.getElementById('santri-empty').classList.remove('d-none');
    document.getElementById('santri-info').classList.add('d-none');
}

document.querySelectorAll('.session-radio').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.getElementById('session-id').value = this.value;
    });
});

document.getElementById('form-absen').addEventListener('submit', function() {
    document.getElementById('btn-submit').disabled = true;
    document.getElementById('btn-submit').innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
});
</script>
@endpush
</x-app-layout>
