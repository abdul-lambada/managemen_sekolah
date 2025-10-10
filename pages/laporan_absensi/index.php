<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Hadir</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($totalHadir) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Total Tidak Hadir</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($totalTidakHadir) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-times fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
        </div>
        <div class="card-body">
            <form class="form-inline mb-3" method="GET" action="<?= route('laporan_absensi') ?>">
                <input type="hidden" name="page" value="laporan_absensi">
                <div class="form-group mr-2">
                    <label for="periode" class="mr-2">Periode</label>
                    <select class="form-control" id="periode" name="periode">
                        <?php foreach (['Harian', 'Mingguan', 'Bulanan'] as $option): ?>
                            <option value="<?= $option ?>" <?= $option === ($periode ?? 'Bulanan') ? 'selected' : '' ?>>
                                <?= $option ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mr-2">
                    <label for="start" class="mr-2">Mulai</label>
                    <input type="date" class="form-control" id="start" name="start" value="<?= sanitize($start ?? '') ?>">
                </div>
                <div class="form-group mr-2">
                    <label for="end" class="mr-2">Akhir</label>
                    <input type="date" class="form-control" id="end" name="end" value="<?= sanitize($end ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary mr-2">Terapkan</button>
                <a href="<?= route('laporan_absensi') ?>" class="btn btn-light mr-2">Reset</a>
                <a href="<?= route('laporan_absensi', ['periode' => $periode, 'start' => $start, 'end' => $end, 'export' => 'csv']) ?>" class="btn btn-success mr-2" target="_blank">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a>
                <a href="<?= route('laporan_absensi', ['periode' => $periode, 'start' => $start, 'end' => $end, 'export' => 'pdf']) ?>" class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
            </form>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Grafik Kehadiran</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="attendanceReportChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Ringkasan</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($reports as $report): ?>
                                    <li class="mb-2">
                                        <strong><?= sanitize($report['periode']) ?>:</strong>
                                        <?= indo_date($report['tanggal_mulai']) ?> - <?= indo_date($report['tanggal_akhir']) ?><br>
                                        Hadir: <?= number_format($report['jumlah_hadir']) ?> | Tidak Hadir: <?= number_format($report['jumlah_tidak_hadir']) ?>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (empty($reports)): ?>
                                    <li>Tidak ada data untuk filter ini.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="tableLaporanAbsensi" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Akhir</th>
                            <th>Jumlah Hadir</th>
                            <th>Jumlah Tidak Hadir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><?= sanitize($report['periode']) ?></td>
                                <td><?= indo_date($report['tanggal_mulai']) ?></td>
                                <td><?= indo_date($report['tanggal_akhir']) ?></td>
                                <td><?= number_format($report['jumlah_hadir']) ?></td>
                                <td><?= number_format($report['jumlah_tidak_hadir']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($reports)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Belum ada data laporan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const reportData = <?= json_encode($reports, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    document.addEventListener('DOMContentLoaded', () => {
        $('#tableLaporanAbsensi').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/id.json'
            }
        });

        const ctx = document.getElementById('attendanceReportChart');
        if (ctx && reportData.length) {
            const labels = reportData.map(item => `${item.periode} (${item.tanggal_mulai} - ${item.tanggal_akhir})`);
            const hadirData = reportData.map(item => parseInt(item.jumlah_hadir, 10));
            const tidakHadirData = reportData.map(item => parseInt(item.jumlah_tidak_hadir, 10));

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Hadir',
                            backgroundColor: '#1cc88a',
                            data: hadirData,
                        },
                        {
                            label: 'Tidak Hadir',
                            backgroundColor: '#e74a3b',
                            data: tidakHadirData,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            ticks: {
                                autoSkip: false,
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                            }
                        }
                    }
                }
            });
        }
    });
</script>
