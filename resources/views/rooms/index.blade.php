<x-app-layout>
    @php
        $statusBadgeClasses = [
            'active' => 'bg-success-lt text-success',
            'inactive' => 'bg-secondary-lt text-secondary',
        ];
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="page-title">Manajemen Kamar</h2>
            <div class="text-secondary mt-1">Kelola master kamar, kapasitas, dan penempatan santri.</div>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-sm-6 col-lg-6">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Total Kamar</div>
                <div class="fs-2 fw-bold">{{ number_format($roomStats['total']) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-6">
            <div class="card card-body">
                <div class="text-uppercase text-secondary small">Total Kapasitas</div>
                <div class="fs-2 fw-bold">{{ number_format((float) $roomStats['capacity']) }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between gap-3 w-100">
                <div>
                    <h3 class="card-title">Daftar Kamar</h3>
                    <div class="text-secondary small mt-2">Menampilkan {{ $rooms->total() }} kamar berdasarkan filter aktif.</div>
                </div>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="open-create-room-modal"
                    data-bs-toggle="modal"
                    data-bs-target="#createRoomModal"
                >
                    Tambah Kamar
                </button>
            </div>
        </div>

        <div class="card-body border-bottom">
            <form method="GET" action="{{ route('rooms.index') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="q" class="form-label">Cari Kamar</label>
                    <input id="q" name="q" type="text" class="form-control" value="{{ $filters['q'] }}" placeholder="Nama kamar atau asrama">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua</option>
                        @foreach ($statusOptions as $statusOption)
                            <option value="{{ $statusOption['value'] }}" @selected($filters['status'] === $statusOption['value'])>
                                {{ $statusOption['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                        <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Kamar</th>
                        <th>Kapasitas</th>
                        <th>Santri</th>
                        <th>Status</th>
                        <th>Santri</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rooms as $room)
                        @php
                            $capacity = $room->capacity ? (int) $room->capacity : null;
                            $activeCount = (int) $room->active_santris_count;
                            $remaining = $capacity ? max(0, $capacity - $activeCount) : null;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $room->name }}</div>
                                @if ($room->description)
                                    <div class="text-secondary small">{{ $room->description }}</div>
                                @endif
                            </td>
                            <td>{{ $capacity ? number_format($capacity) : 'Tidak dibatasi' }}</td>
                            <td>
                                <div>{{ number_format($activeCount) }} santri</div>
                                <div class="text-secondary small">
                                    {{ $remaining === null ? 'Kapasitas terbuka' : 'Sisa '.number_format($remaining) }}
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $statusBadgeClasses[$room->status] ?? 'bg-secondary-lt text-secondary' }}">
                                    {{ $room->statusLabel() }}
                                </span>
                            </td>
                            <td>
                                @if ($room->santris->isNotEmpty())
                                    <div class="d-flex flex-column gap-1">
                                        @foreach ($room->santris as $santri)
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                <span class="small">{{ $santri->full_name }}</span>
                                                <form method="POST" action="{{ route('rooms.santris.release', [$room, $santri]) }}" onsubmit="return confirm('Keluarkan santri ini dari kamar?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger p-0 small">Keluarkan</button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-secondary small">Belum ada santri.</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignRoomModal{{ $room->id }}">
                                        Tempatkan
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editRoomModal{{ $room->id }}">
                                        Edit
                                    </button>
                                    @if ($room->santris_count === 0)
                                        <form method="POST" action="{{ route('rooms.destroy', $room) }}" onsubmit="return confirm('Hapus kamar ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">Hapus</button>
                                        </form>
                                    @endif
                                </div>

                                <div class="modal modal-blur fade" id="assignRoomModal{{ $room->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('rooms.santris.assign', $room) }}">
                                                @csrf
                                                <input type="hidden" name="assigning_room_id" value="{{ $room->id }}">

                                                <div class="modal-header">
                                                    <div>
                                                        <h5 class="modal-title">Tempatkan Santri</h5>
                                                        <div class="text-secondary small mt-1">{{ $room->name }}</div>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    @if ($assignableSantris->isEmpty())
                                                        <div class="text-secondary text-center py-3">Tidak ada santri aktif yang belum memiliki kamar.</div>
                                                    @else
                                                        <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                                                            @foreach ($assignableSantris as $santri)
                                                                <label class="list-group-item d-flex align-items-center gap-3">
                                                                    <input type="checkbox" name="santri_ids[]" value="{{ $santri->id }}" class="form-check-input m-0" @checked(in_array((string) $santri->id, old('santri_ids', []), true))>
                                                                    <div>
                                                                        <div class="fw-semibold">{{ $santri->full_name }}</div>
                                                                        <div class="text-secondary small">NIS: {{ $santri->nis }}</div>
                                                                    </div>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                    @if ($errors->assignRoomSantri->has('santri_ids'))
                                                        <div class="invalid-feedback d-block mt-2">{{ $errors->assignRoomSantri->first('santri_ids') }}</div>
                                                    @endif
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Simpan Penempatan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal modal-blur fade" id="editRoomModal{{ $room->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('rooms.update', $room) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="editing_room_id" value="{{ $room->id }}">

                                                <div class="modal-header">
                                                    <div>
                                                        <h5 class="modal-title">Edit Kamar</h5>
                                                        <div class="text-secondary small mt-1">{{ $room->name }}</div>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    @include('rooms.partials.form-fields', [
                                                        'formPrefix' => 'edit_'.$room->id,
                                                        'room' => $room,
                                                        'statusOptions' => $statusOptions,
                                                        'errorBag' => $errors->updateRoom,
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-secondary">Belum ada kamar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($rooms->hasPages())
            <div class="card-footer">
                {{ $rooms->links() }}
            </div>
        @endif
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <div>
                <h3 class="card-title">Riwayat Pindah Kamar</h3>
                <div class="text-secondary small mt-2">Menampilkan 10 penempatan atau perpindahan kamar terbaru.</div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Santri</th>
                        <th>Dari</th>
                        <th>Ke</th>
                        <th>Dipindahkan Oleh</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentRoomTransfers as $roomTransfer)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $roomTransfer->santri?->full_name ?? '-' }}</div>
                                <div class="text-secondary small">NIS: {{ $roomTransfer->santri?->nis ?? '-' }}</div>
                            </td>
                            <td>{{ $roomTransfer->from_room_name ?: 'Belum berkamar' }}</td>
                            <td>{{ $roomTransfer->to_room_name ?: 'Belum berkamar' }}</td>
                            <td>{{ $roomTransfer->mover?->name ?? '-' }}</td>
                            <td class="text-secondary">{{ $roomTransfer->moved_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-secondary">Belum ada riwayat pindah kamar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal modal-blur fade" id="createRoomModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('rooms.store') }}">
                    @csrf

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Tambah Kamar</h5>
                            <div class="text-secondary small mt-1">Kamar dipakai untuk mengatur kapasitas dan penempatan santri.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        @include('rooms.partials.form-fields', [
                            'formPrefix' => 'create',
                            'room' => null,
                            'statusOptions' => $statusOptions,
                            'errorBag' => $errors->createRoom,
                        ])
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Kamar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if ($errors->createRoom->any())
                document.getElementById('open-create-room-modal')?.click();
            @endif

            @if ($errors->updateRoom->any() && old('editing_room_id'))
                const editRoomModalElement = document.getElementById('editRoomModal{{ old('editing_room_id') }}');

                if (editRoomModalElement && window.bootstrap?.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(editRoomModalElement).show();
                }
            @endif

            @if ($errors->assignRoomSantri->any() && old('assigning_room_id'))
                const assignRoomModalElement = document.getElementById('assignRoomModal{{ old('assigning_room_id') }}');

                if (assignRoomModalElement && window.bootstrap?.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(assignRoomModalElement).show();
                }
            @endif
        });
    </script>
</x-app-layout>
