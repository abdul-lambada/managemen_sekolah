<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Rekap Absensi Siswa per Kelas</h6>
        </div>
        <div class="card-body">
            <form class="form-inline mb-3" method="GET" action="<?= route('laporan_kelas') ?>">
                <input type="hidden" name="page" value="laporan_kelas">
                <div class="form-group mr-2">
                    <label for="kelas" class="mr-2">Kelas</label>
                    <select class="form-control" id="kelas" name="kelas">
                        <option value="">Semua</option>
                        <?php foreach ($kelasOptions as $kelasOption): ?>
                            <?php $value = (int) $kelasOption['id_kelas']; ?>
                            <option value="<?= $value ?>" <?= ($kelasId ?? null) === $value ? 'selected' : '' ?>>
                                <?= sanitize(($kelasOption['nama_jurusan'] ?? '') . ' - ' . $kelasOption['nama_kelas']) ?>
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
                <button type="submit" class="btn btn-primary mr-2">Filter</button>
                <a href="<?= route('laporan_kelas') ?>" class="btn btn-light mr-2">Reset</a>
                <a href="<?= route('laporan_kelas', ['kelas' => $kelasId, 'start' => $start, 'end' => $end, 'export' => 'csv']) ?>" class="btn btn-success mr-2" target="_blank">
                    <i class="fas fa-file-csv"></i> CSV
                </a>
                <a href="<?= route('laporan_kelas', ['kelas' => $kelasId, 'start' => $start, 'end' => $end, 'export' => 'pdf']) ?>" class="btn btn-danger mr-2" target="_blank">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                <a href="<?= route('laporan_kelas', ['kelas' => $kelasId, 'start' => $start, 'end' => $end, 'export' => 'excel']) ?>" class="btn btn-warning text-white" target="_blank">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered" id="tableRekapKelas" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Jurusan</th>
                            <th>Kelas</th>
                            <th>Hadir</th>
                            <th>Izin</th>
                            <th>Sakit</th>
                            <th>Alpa</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $index => $row): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= sanitize($row['nama_jurusan']) ?></td>
                                <td><?= sanitize($row['nama_kelas']) ?></td>
                                <td><?= number_format((int) $row['hadir']) ?></td>
                                <td><?= number_format((int) $row['izin']) ?></td>
                                <td><?= number_format((int) $row['sakit']) ?></td>
                                <td><?= number_format((int) $row['alpa']) ?></td>
                                <td><?= number_format((int) $row['total']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data rekap.</td>
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
        $('#tableRekapKelas').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/id.json'
            }
        });
    });
</script>
