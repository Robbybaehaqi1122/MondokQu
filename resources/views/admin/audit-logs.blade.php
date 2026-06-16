<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Audit Trail</h2>
            <div class="text-secondary mt-1">
                Catatan otomatis seluruh request POST / PUT / PATCH / DELETE yang diproses sistem.
            </div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="row g-3">
                <div class="col-sm-6 col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Total Request</div>
                            <div class="h1 mb-1">{{ $summary['total'] }}</div>
                            <div class="text-secondary small">Dalam cakupan akses Anda.</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Hasil Filter</div>
                            <div class="h1 mb-1">{{ $summary['filtered'] }}</div>
                            <div class="text-secondary small">Sesuai pencarian aktif.</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Hari Ini</div>
                            <div class="h1 mb-1">{{ $summary['today'] }}</div>
                            <div class="text-secondary small">Request tercatat hari ini.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Audit Trail Request</h3>
                        <p class="text-secondary mb-0">Seluruh request POST, PUT, PATCH, dan DELETE yang melalui middleware audit.</p>
                    </div>
                </div>
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('admin.audit-logs') }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="audit-search" class="form-label">Cari</label>
                            <input id="audit-search" type="search" name="search" value="{{ $filters['search'] }}" class="form-control" placeholder="URL, data request, IP address">
                        </div>
                        <div class="col-md-2">
                            <label for="audit-method" class="form-label">Method</label>
                            <select id="audit-method" name="method" class="form-select">
                                <option value="">Semua method</option>
                                @foreach ($methodOptions as $method)
                                    <option value="{{ $method }}" @selected($filters['method'] === $method)>{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="audit-user" class="form-label">User</label>
                            <select id="audit-user" name="user_id" class="form-select">
                                <option value="">Semua user</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected((int) $filters['user_id'] === $user->id)>{{ $user->name }} (@{{ $user->username }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="audit-date-from" class="form-label">Dari</label>
                            <input id="audit-date-from" type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="audit-date-to" class="form-label">Sampai</label>
                            <input id="audit-date-to" type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control">
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-filter me-1"></i>
                                Terapkan Filter
                            </button>
                            <a href="{{ route('admin.audit-logs') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>User</th>
                                <th>Method</th>
                                <th>URL</th>
                                <th>Status</th>
                                <th>Durasi</th>
                                <th>IP</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="text-secondary small text-nowrap">{{ $log->created_at->translatedFormat('d M Y H:i:s') }}</td>
                                    <td>
                                        @if ($log->user)
                                            <div class="fw-semibold">{{ $log->user->name }}</div>
                                            <div class="text-secondary small">@{{ $log->user->username }}</div>
                                        @else
                                            <span class="text-secondary">Guest / System</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $methodBadge = match ($log->method) {
                                                'POST' => 'bg-green-lt text-green',
                                                'PUT' => 'bg-blue-lt text-blue',
                                                'PATCH' => 'bg-orange-lt text-orange',
                                                'DELETE' => 'bg-red-lt text-red',
                                                default => 'bg-secondary-lt text-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $methodBadge }}">{{ $log->method }}</span>
                                    </td>
                                    <td class="text-secondary small" style="max-width:300px">
                                        <div class="text-truncate" title="{{ $log->url }}">
                                            {{ $log->url }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge @if ($log->response_status >= 200 && $log->response_status < 300) bg-success-lt text-success @elseif ($log->response_status >= 400 && $log->response_status < 500) bg-warning-lt text-warning @elseif ($log->response_status >= 500) bg-danger-lt text-danger @else bg-secondary-lt text-secondary @endif">
                                            {{ $log->response_status ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="text-secondary small text-nowrap">
                                        @if ($log->duration_ms !== null)
                                            @if ($log->duration_ms >= 1000)
                                                {{ number_format($log->duration_ms / 1000, 2) }}s
                                            @else
                                                {{ $log->duration_ms }}ms
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-secondary small">{{ $log->ip_address ?? '-' }}</td>
                                    <td>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#audit-detail-{{ $log->id }}"
                                            title="Lihat detail"
                                        >
                                            <i class="ti ti-eye"></i>
                                        </button>

                                        <div class="modal modal-blur fade" id="audit-detail-{{ $log->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Detail Audit Request</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <strong>Waktu:</strong>
                                                            <span class="text-secondary ms-2">{{ $log->created_at->translatedFormat('d M Y H:i:s') }}</span>
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Method:</strong>
                                                            <span class="badge {{ $methodBadge }} ms-2">{{ $log->method }}</span>
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>URL:</strong>
                                                            <div class="text-secondary mt-1" style="word-break:break-all">{{ $log->url }}</div>
                                                        </div>
                                                        <div class="row g-3 mb-3">
                                                            <div class="col-md-4">
                                                                <strong>Status:</strong>
                                                                <span class="ms-2">{{ $log->response_status ?? '-' }}</span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <strong>Durasi:</strong>
                                                                <span class="ms-2">
                                                                    @if ($log->duration_ms !== null)
                                                                        @if ($log->duration_ms >= 1000)
                                                                            {{ number_format($log->duration_ms / 1000, 2) }}s
                                                                        @else
                                                                            {{ $log->duration_ms }}ms
                                                                        @endif
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <strong>IP:</strong>
                                                                <span class="ms-2">{{ $log->ip_address ?? '-' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>User Agent:</strong>
                                                            <div class="text-secondary mt-1 small" style="word-break:break-all">{{ $log->user_agent ?? '-' }}</div>
                                                        </div>
                                                        <div>
                                                            <strong>Request Data:</strong>
                                                            @if ($log->request_data)
                                                                <pre class="bg-dark text-light p-3 mt-2 rounded" style="font-size:12px;overflow-x:auto;max-height:300px"><code>{{ json_encode($log->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                                            @else
                                                                <div class="text-secondary mt-2">Tidak ada data request (kosong).</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-secondary">Belum ada audit log yang tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($logs->hasPages())
                    <div class="card-footer">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
