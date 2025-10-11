<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Filter Absensi</h6>
        </div>
        <div class="card-body">
            <form class="form-inline mb-4" method="GET" action="<?= route('portal_siswa_absensi') ?>">
                <input type="hidden" name="page" value="portal_siswa_absensi">
                <div class="form-group mr-2">
                    <label for="start" class="mr-2">Mulai</label>
                    <input type="date" class="form-control" id="start" name="start" value="<?= sanitize($start ?? '') ?>">
                </div>
                <div class="form-group mr-2">
                    <label for="end" class="mr-2">Akhir</label>
                    <input type="date" class="form-control" id="end" name="end" value="<?= sanitize($end ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary mr-2">Terapkan</button>
                <a href="<?= route('portal_siswa_absensi') ?>" class="btn btn-light">Reset</a>
            </form>

            <div class="row mb-4">
                <div class="col-md-3 col-6 mb-3">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Hadir</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['Hadir'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Izin</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['Izin'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Sakit</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['Sakit'] ?? 0) ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Alpa / Terlambat</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format(($summary['Alpa'] ?? 0) + ($summary['Terlambat'] ?? 0)) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="tablePortalAbsensi" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $row): ?>
                            <tr>
                                <td><?= indo_date($row['tanggal']) ?></td>
                                <td><?= attendance_badge($row['status_kehadiran']) ?></td>
                                <td><?= sanitize($row['jam_masuk'] ?? '-') ?></td>
                                <td><?= sanitize($row['jam_keluar'] ?? '-') ?></td>
                                <td><?= sanitize($row['keterangan'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Belum ada data untuk rentang ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        $('#tablePortalAbsensi').DataTable({
            order: [[0, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/id.json'
            }
        });
    });
</script>
