<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Queue Monitoring</h2>
            <div class="text-secondary mt-1">
                Pantau antrean job, job gagal, dan job yang nyangkut.
            </div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-12">
            <div class="row g-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Antrean Aktif</div>
                            <div class="h1 mb-1">{{ $pendingTotal }}</div>
                            <div class="text-secondary small">Job menunggu diproses.</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Job Gagal</div>
                            <div class="h1 mb-1 @if ($failedTotal > 0) text-danger @endif">{{ $failedTotal }}</div>
                            <div class="text-secondary small">Total gagal sepanjang masa.</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Tertahan</div>
                            <div class="h1 mb-1 @if ($stuckJobs->count() > 0) text-warning @endif">{{ $stuckJobs->count() }}</div>
                            <div class="text-secondary small">Job reserved > 30 menit.</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-secondary small text-uppercase fw-bold">Queue Driver</div>
                            <div class="h1 mb-1">{{ config('queue.default') }}</div>
                            <div class="text-secondary small">{{ config('queue.default') === 'database' ? 'MySQL / MariaDB' : 'Redis / lainnya' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($pendingJobs->isNotEmpty())
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Antrean per Queue</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Queue</th>
                                    <th class="text-end">Jumlah</th>
                                    <th>Job Tertua</th>
                                    <th>Job Terbaru</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingJobs as $item)
                                    <tr>
                                        <td class="fw-semibold">{{ $item->queue }}</td>
                                        <td class="text-end">{{ $item->total }}</td>
                                        <td class="text-secondary small">{{ date('d M Y H:i:s', $item->oldest) }}</td>
                                        <td class="text-secondary small">{{ date('d M Y H:i:s', $item->newest) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if ($stuckJobs->isNotEmpty())
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title text-warning">Job Tertahan (Reserved > 30 menit)</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Queue</th>
                                    <th>Job ID</th>
                                    <th>Attempts</th>
                                    <th>Reserved</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stuckJobs as $job)
                                    <tr>
                                        <td>{{ $job->queue }}</td>
                                        <td class="text-secondary small">{{ $job->id }}</td>
                                        <td>{{ $job->attempts }}</td>
                                        <td class="text-secondary small">{{ date('d M Y H:i:s', $job->reserved_at) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Riwayat Job Gagal</h3>
                        @if ($failedByQueue->isNotEmpty())
                            <p class="text-secondary mb-0">
                                Per Queue:
                                @foreach ($failedByQueue as $item)
                                    <span class="badge bg-danger-lt text-danger me-1">{{ $item->queue }}: {{ $item->total }}</span>
                                @endforeach
                            </p>
                        @endif
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Waktu Gagal</th>
                                <th>Queue</th>
                                <th>Job</th>
                                <th>Exception</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($failedJobs as $job)
                                <tr>
                                    <td class="text-secondary small text-nowrap">{{ date('d M Y H:i:s', strtotime($job->failed_at)) }}</td>
                                    <td>{{ $job->queue }}</td>
                                    <td class="text-secondary small" style="max-width:200px">
                                        <div class="text-truncate" title="{{ $job->payload }}">
                                            @php
                                                $payload = json_decode($job->payload, true);
                                                $displayName = $payload['displayName'] ?? class_basename($payload['data']['commandName'] ?? 'Unknown');
                                            @endphp
                                            {{ $displayName }}
                                        </div>
                                    </td>
                                    <td style="max-width:400px">
                                        <div class="text-truncate small text-danger" title="{{ $job->exception }}">
                                            {{ str($job->exception)->limit(120) }}
                                        </div>
                                    </td>
                                    <td>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#failed-detail-{{ $job->id }}"
                                            title="Lihat detail exception"
                                        >
                                            <i class="ti ti-eye"></i>
                                        </button>

                                        <div class="modal modal-blur fade" id="failed-detail-{{ $job->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Detail Job Gagal</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <strong>UUID:</strong>
                                                            <span class="text-secondary ms-2">{{ $job->uuid }}</span>
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Queue:</strong>
                                                            <span class="ms-2">{{ $job->queue }}</span>
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Connection:</strong>
                                                            <span class="ms-2">{{ $job->connection }}</span>
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Waktu Gagal:</strong>
                                                            <span class="text-secondary ms-2">{{ date('d M Y H:i:s', strtotime($job->failed_at)) }}</span>
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Exception:</strong>
                                                            <pre class="bg-dark text-light p-3 mt-2 rounded" style="font-size:12px;overflow-x:auto;max-height:400px;white-space:pre-wrap;word-break:break-all"><code>{{ $job->exception }}</code></pre>
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
                                    <td colspan="5" class="text-secondary">Belum ada job yang gagal. Mantap!</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($failedJobs->hasPages())
                    <div class="card-footer">
                        {{ $failedJobs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
