<div class="row row-cards">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Grafik Perkembangan Nilai</h3>
                <div class="card-actions">
                    <button type="button" class="btn btn-outline-primary" id="download-chart-btn">
                        <i class="ti ti-download me-1"></i> Download Grafik
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Filter Mata Pelajaran</label>
                        <select id="mapel-filter" class="form-select" multiple>
                            @foreach ($mapels as $m)
                                <option value="{{ $m->id }}" selected>{{ $m->nama }}</option>
                            @endforeach
                        </select>
                        <div class="text-secondary small mt-1">Klik untuk memilih/deselect. Ctrl+klik untuk multi-select.</div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="show-class-avg" checked>
                            <label class="form-check-label" for="show-class-avg">Tampilkan Rata-rata Kelas</label>
                        </div>
                    </div>
                </div>

                <div class="chart-container" style="height: 350px;">
                    <canvas id="perkembangan-nilai-chart"></canvas>
                </div>

                <div id="chart-no-data" class="text-secondary text-center py-5 d-none">
                    Belum ada data nilai untuk santri ini.
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tabel Nilai per Semester</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table" id="nilai-table">
                    <thead>
                        <tr>
                            <th>Semester</th>
                            @foreach ($mapels as $m)
                                <th class="mapel-col" data-mapel-id="{{ $m->id }}">{{ $m->nama }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="nilai-table-body">
                        <tr>
                            <td colspan="{{ $mapels->count() + 1 }}" class="text-secondary text-center">
                                Memuat data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('perkembangan-nilai-chart');
        const noData = document.getElementById('chart-no-data');
        const mapelFilter = document.getElementById('mapel-filter');
        const showClassAvg = document.getElementById('show-class-avg');
        const tableBody = document.getElementById('nilai-table-body');
        const downloadBtn = document.getElementById('download-chart-btn');

        let chartInstance = null;
        let allData = null;

        const colors = [
            '#206bc4', '#2fb344', '#d63939', '#f59f00', '#17a2b8',
            '#6f42c1', '#e83e8c', '#20c997', '#fd7e14', '#6610f2',
            '#dc3545', '#198754', '#0dcaf0', '#ffc107', '#0d6efd'
        ];

        function fetchData() {
            const selectedIds = Array.from(mapelFilter.selectedOptions).map(o => o.value);
            const params = new URLSearchParams({ santri_id: '{{ $santri->id }}' });
            selectedIds.forEach(id => params.append('mata_pelajaran_id[]', id));

            fetch('{{ route("akademik.rapor.chart-data") }}?' + params.toString())
                .then(r => r.json())
                .then(data => {
                    allData = data;
                    renderChart();
                    renderTable();
                });
        }

        function renderChart() {
            if (!allData || allData.series.length === 0) {
                canvas.classList.add('d-none');
                noData.classList.remove('d-none');
                return;
            }
            canvas.classList.remove('d-none');
            noData.classList.add('d-none');

            if (chartInstance) chartInstance.destroy();

            const datasets = [];

            allData.series.forEach((s, i) => {
                const c = colors[i % colors.length];
                datasets.push({
                    label: s.name,
                    data: s.data,
                    borderColor: c,
                    backgroundColor: c + '20',
                    fill: false,
                    tension: 0.3,
                    spanGaps: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                });

                if (showClassAvg.checked && s.rata_rata_kelas) {
                    datasets.push({
                        label: s.name + ' (Rata Kelas)',
                        data: s.rata_rata_kelas,
                        borderColor: c,
                        backgroundColor: c + '10',
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.3,
                        spanGaps: true,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointStyle: 'triangle',
                    });
                }
            });

            chartInstance = new Chart(canvas, {
                type: 'line',
                data: {
                    labels: allData.semesters,
                    datasets: datasets,
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 16,
                                usePointStyle: true,
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    let label = ctx.dataset.label || '';
                                    if (ctx.parsed.y !== null && ctx.parsed.y !== undefined) {
                                        label += ': ' + ctx.parsed.y;
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            min: 0,
                            max: 100,
                            title: { display: true, text: 'Nilai' }
                        }
                    }
                }
            });
        }

        function renderTable() {
            if (!allData || allData.semesters.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="{{ $mapels->count() + 1 }}" class="text-secondary text-center">Belum ada data nilai.</td></tr>';
                return;
            }

            const selectedIds = new Set(Array.from(mapelFilter.selectedOptions).map(o => o.value));
            const visibleCols = Array.from(document.querySelectorAll('.mapel-col'))
                .filter(col => selectedIds.has(col.dataset.mapelId));

            let html = '';
            allData.semesters.forEach(sem => {
                html += '<tr>';
                html += '<td class="fw-semibold">' + sem + '</td>';
                allData.series.forEach(s => {
                    const idx = allData.semesters.indexOf(sem);
                    const val = s.data[idx];
                    const cls = val !== null && val !== undefined ? (val >= 70 ? 'text-success' : 'text-danger') : 'text-secondary';
                    html += '<td class="' + cls + '">' + (val !== null && val !== undefined ? val : '-') + '</td>';
                });
                html += '</tr>';
            });

            tableBody.innerHTML = html;
        }

        mapelFilter.addEventListener('change', fetchData);
        showClassAvg.addEventListener('change', renderChart);

        downloadBtn.addEventListener('click', function () {
            if (!chartInstance) return;
            const link = document.createElement('a');
            link.download = 'perkembangan-nilai-{{ $santri->full_name }}.png';
            link.href = chartInstance.toBase64Image('image/png', 1);
            link.click();
        });

        fetchData();
    });
</script>
@endpush
