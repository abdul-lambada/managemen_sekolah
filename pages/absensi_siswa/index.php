<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Absensi Siswa</h6>
        </div>
        <div class="card-body">
            <form class="form-inline mb-3" method="GET" action="<?= route('absensi_siswa') ?>">
                <input type="hidden" name="page" value="absensi_siswa">
                <div class="form-group mr-2">
                    <label for="start" class="mr-2">Mulai</label>
                    <input type="date" class="form-control" id="start" name="start" value="<?= sanitize($start ?? '') ?>">
                </div>
                <div class="form-group mr-2">
                    <label for="end" class="mr-2">Akhir</label>
                    <input type="date" class="form-control" id="end" name="end" value="<?= sanitize($end ?? '') ?>">
                </div>
                <div class="form-group mr-2">
                    <label for="kelas" class="mr-2">Kelas</label>
                    <select class="form-control" id="kelas" name="kelas">
                        <option value="">Semua</option>
                        <?php foreach ($kelasOptions as $kelas): ?>
                            <option value="<?= (int) $kelas['id_kelas'] ?>" <?= $kelasId === (int) $kelas['id_kelas'] ? 'selected' : '' ?>>
                                <?= sanitize($kelas['nama_jurusan'] . ' - ' . $kelas['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mr-2">Filter</button>
                <a href="<?= route('absensi_siswa') ?>" class="btn btn-light mr-2">Reset</a>
                <a href="<?= route('absensi_siswa', ['start' => $start, 'end' => $end, 'kelas' => $kelasId, 'export' => 'csv']) ?>" class="btn btn-success mr-2" target="_blank">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a>
                <a href="<?= route('absensi_siswa', ['start' => $start, 'end' => $end, 'kelas' => $kelasId, 'export' => 'pdf']) ?>" class="btn btn-danger" target="_blank">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered" id="tableAbsensiSiswa" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tanggal</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $index => $row): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= indo_date($row['tanggal']) ?></td>
                                <td><?= sanitize($row['nama_siswa']) ?></td>
                                <td><?= sanitize($row['nama_kelas']) ?></td>
                                <td><?= attendance_badge($row['status_kehadiran']) ?></td>
                                <td><?= sanitize($row['jam_masuk'] ?? '-') ?></td>
                                <td><?= sanitize($row['jam_keluar'] ?? '-') ?></td>
                                <td><?= sanitize($row['catatan'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data absensi.</td>
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
        $('#tableAbsensiSiswa').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/id.json'
            }
        });
    });
</script>
