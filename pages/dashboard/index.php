<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> Export Data
        </a>
    </div>

    <div class="row">
        <?php if (!empty($automationStatus)): ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <?php $wa = $automationStatus['whatsapp']; ?>
                <div class="card border-left-<?= sanitize($wa['badge']) ?> shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-<?= sanitize($wa['badge']) ?> text-uppercase mb-1">
                                    WhatsApp Dispatch
                                </div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800">
                                    <?= sanitize($wa['label']) ?>
                                </div>
                                <?php if (!empty($wa['meta']['success']) || !empty($wa['meta']['failure'])): ?>
                                    <small class="text-muted">
                                        Sukses: <?= (int) ($wa['meta']['success'] ?? 0) ?> | Gagal: <?= (int) ($wa['meta']['failure'] ?? 0) ?>
                                    </small><br>
                                <?php endif; ?>
                                <small class="text-muted">
                                    Terakhir: <?= $wa['updated_at'] ? indo_datetime($wa['updated_at']) : '-' ?>
                                </small>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <?php $fp = $automationStatus['fingerprint']; ?>
                <div class="card border-left-<?= sanitize($fp['badge']) ?> shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-<?= sanitize($fp['badge']) ?> text-uppercase mb-1">
                                    Sync Fingerprint
                                </div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800">
                                    <?= sanitize($fp['label']) ?>
                                </div>
                                <?php if (!empty($fp['meta']['total_success']) || !empty($fp['meta']['total_failure'])): ?>
                                    <small class="text-muted">
                                        Sukses: <?= (int) ($fp['meta']['total_success'] ?? 0) ?> | Gagal: <?= (int) ($fp['meta']['total_failure'] ?? 0) ?>
                                    </small><br>
                                <?php endif; ?>
                                <small class="text-muted">
                                    Terakhir: <?= $fp['updated_at'] ? indo_datetime($fp['updated_at']) : '-' ?>
                                </small>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-fingerprint fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php
        $statIcons = [
            'guru' => 'fa-user-tie',
            'siswa' => 'fa-user-graduate',
            'kelas' => 'fa-door-open',
            'jurusan' => 'fa-layer-group',
            'absensi_guru' => 'fa-chalkboard-teacher',
            'absensi_siswa' => 'fa-users',
        ];
        ?>
        <?php foreach ($stats as $key => $value): ?>
            <div class="col-xl-2 col-md-4 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    <?= strtoupper(str_replace('_', ' ', $key)) ?>
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format((int) $value) ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas <?= $statIcons[$key] ?? 'fa-database' ?> fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Distribusi Absensi Siswa</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Ringkasan Status WhatsApp</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="whatsappChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small" id="whatsappLegend"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Absensi Terbaru</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Waktu</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentAttendance as $row): ?>
                                    <tr>
                                        <td><?= sanitize($row['user_name']) ?></td>
                                        <td><?= indo_datetime($row['timestamp']) ?></td>
                                        <td><?= sanitize($row['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recentAttendance)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center">Belum ada data absensi.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Log WhatsApp Terbaru</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Nomor</th>
                                    <th>Status</th>
                                    <th>Jenis</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentWhatsapp as $row): ?>
                                    <tr>
                                        <td><?= sanitize($row['phone_number']) ?></td>
                                        <td><?= sanitize($row['status']) ?></td>
                                        <td><?= sanitize($row['message_type']) ?></td>
                                        <td><?= indo_datetime($row['created_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recentWhatsapp)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Belum ada log WhatsApp.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistik Sistem</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Kunci</th>
                                    <th>Nilai</th>
                                    <th>Diperbarui</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($systemStats as $row): ?>
                                    <tr>
                                        <td><?= sanitize($row['stat_key']) ?></td>
                                        <td><?= sanitize($row['stat_value']) ?></td>
                                        <td><?= indo_datetime($row['updated_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($systemStats)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center">Belum ada statistik.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const attendanceData = <?= json_encode($attendanceChart, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const whatsappData = <?= json_encode($whatsappChart, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    document.addEventListener('DOMContentLoaded', () => {
        const attendanceCtx = document.getElementById('attendanceChart');
        if (attendanceCtx && attendanceData.labels.length) {
            new Chart(attendanceCtx, {
                type: 'line',
                data: {
                    labels: attendanceData.labels,
                    datasets: [{
                        label: 'Total',
                        data: attendanceData.data,
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        tension: 0.4,
                        fill: true,
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }

        const whatsappCtx = document.getElementById('whatsappChart');
        if (whatsappCtx && whatsappData.labels.length) {
            const colors = ['#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'];
            new Chart(whatsappCtx, {
                type: 'doughnut',
                data: {
                    labels: whatsappData.labels,
                    datasets: [{
                        data: whatsappData.data,
                        backgroundColor: colors,
                        hoverBackgroundColor: colors,
                        hoverBorderColor: 'rgba(234, 236, 244, 1)',
                    }]
                },
                options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });

            const legendContainer = document.getElementById('whatsappLegend');
            if (legendContainer) {
                legendContainer.innerHTML = whatsappData.labels.map((label, index) => {
                    return `<span class="mr-2"><i class="fas fa-circle" style="color:${colors[index % colors.length]}"></i> ${label}</span>`;
                }).join('');
            }
        }
    });
</script>
