<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Laporan Keterlambatan Guru</h6>
        </div>
        <div class="card-body">
            <form class="form-inline mb-3" method="GET" action="<?= route('laporan_keterlambatan') ?>">
                <input type="hidden" name="page" value="laporan_keterlambatan">
                <div class="form-group mr-2">
                    <label for="start" class="mr-2">Mulai</label>
                    <input type="date" class="form-control" id="start" name="start" value="<?= sanitize($start ?? '') ?>">
                </div>
                <div class="form-group mr-2">
                    <label for="end" class="mr-2">Akhir</label>
                    <input type="date" class="form-control" id="end" name="end" value="<?= sanitize($end ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary mr-2">Filter</button>
                <a href="<?= route('laporan_keterlambatan') ?>" class="btn btn-light mr-2">Reset</a>
                <a href="<?= route('laporan_keterlambatan', ['start' => $start, 'end' => $end, 'export' => 'csv']) ?>" class="btn btn-success mr-2" target="_blank">
                    <i class="fas fa-file-csv"></i> CSV
                </a>
                <a href="<?= route('laporan_keterlambatan', ['start' => $start, 'end' => $end, 'export' => 'pdf']) ?>" class="btn btn-danger mr-2" target="_blank">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                <a href="<?= route('laporan_keterlambatan', ['start' => $start, 'end' => $end, 'export' => 'excel']) ?>" class="btn btn-warning text-white" target="_blank">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered" id="tableKeterlambatanGuru" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tanggal</th>
                            <th>Guru</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th>Jam Terjadwal</th>
                            <th>Jam Masuk</th>
                            <th>Menit Terlambat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $index => $row): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= indo_date($row['tanggal']) ?></td>
                                <td><?= sanitize($row['nama_guru']) ?></td>
                                <td><?= sanitize($row['nama_mapel']) ?></td>
                                <td><?= sanitize($row['nama_kelas']) ?></td>
                                <td><?= sanitize(substr($row['jam_mulai'], 0, 5) . ' - ' . substr($row['jam_selesai'], 0, 5)) ?></td>
                                <td><?= sanitize($row['jam_masuk'] ?? '-') ?></td>
                                <td><?= number_format((int) ($row['menit_terlambat'] ?? 0)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data keterlambatan.</td>
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
        $('#tableKeterlambatanGuru').DataTable({
            order: [[1, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/id.json'
            }
        });
    });
</script>
