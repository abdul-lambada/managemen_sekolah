<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Hadir</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($summary['Hadir'] ?? 0) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Izin / Sakit</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format(($summary['Izin'] ?? 0) + ($summary['Sakit'] ?? 0)) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-md fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Alpa / Terlambat</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format(($summary['Alpa'] ?? 0) + ($summary['Terlambat'] ?? 0)) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-times fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Profil Saya</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Nama</dt>
                        <dd class="col-7"><?= sanitize($profil['nama_siswa']) ?></dd>
                        <dt class="col-5">NIS</dt>
                        <dd class="col-7"><?= sanitize($profil['nis'] ?? '-') ?></dd>
                        <dt class="col-5">NISN</dt>
                        <dd class="col-7"><?= sanitize($profil['nisn'] ?? '-') ?></dd>
                        <dt class="col-5">Kelas</dt>
                        <dd class="col-7"><?= sanitize($profil['nama_kelas']) ?></dd>
                        <dt class="col-5">Jurusan</dt>
                        <dd class="col-7"><?= sanitize($profil['nama_jurusan']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-8 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Riwayat Absensi Terbaru</h6>
                    <a href="<?= route('portal_siswa_absensi') ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
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
                                <?php foreach ($recent as $row): ?>
                                    <tr>
                                        <td><?= indo_date($row['tanggal']) ?></td>
                                        <td><?= attendance_badge($row['status_kehadiran']) ?></td>
                                        <td><?= sanitize($row['jam_masuk'] ?? '-') ?></td>
                                        <td><?= sanitize($row['jam_keluar'] ?? '-') ?></td>
                                        <td><?= sanitize($row['keterangan'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recent)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Belum ada data absensi.</td>
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
