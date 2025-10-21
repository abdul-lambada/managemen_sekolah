<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Absensi Guru</h6>
        </div>
        <div class="card-body">
            <form class="form-inline mb-3" method="GET" action="<?= route('absensi_guru') ?>">
                <input type="hidden" name="page" value="absensi_guru">
                <div class="form-group mr-2">
                    <label for="start" class="mr-2">Mulai</label>
                    <input type="date" class="form-control" id="start" name="start" value="<?= sanitize($start ?? '') ?>">
                </div>
                <div class="form-group mr-2">
                    <label for="end" class="mr-2">Akhir</label>
                    <input type="date" class="form-control" id="end" name="end" value="<?= sanitize($end ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary mr-2">Filter</button>
                <a href="<?= route('absensi_guru') ?>" class="btn btn-light mr-2">Reset</a>
                <a href="<?= route('absensi_guru', ['start' => $start, 'end' => $end, 'export' => 'csv']) ?>" class="btn btn-success mr-2" target="_blank">
                    <i class="fas fa-file-csv"></i> CSV
                </a>
                <a href="<?= route('absensi_guru', ['start' => $start, 'end' => $end, 'export' => 'pdf']) ?>" class="btn btn-danger mr-2" target="_blank">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                <a href="<?= route('absensi_guru', ['start' => $start, 'end' => $end, 'export' => 'excel']) ?>" class="btn btn-warning text-white" target="_blank">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered" id="tableAbsensiGuru" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tanggal</th>
                            <th>Nama Guru</th>
                            <th>Status</th>
                            <th>Masuk Pagi</th>
                            <th>Pulang Sore</th>
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
                                <td><?= sanitize($row['nama_guru']) ?></td>
                                <td><?= attendance_badge($row['status_kehadiran']) ?></td>
                                <td>
                                    <?php if (!empty($row['daily_check_in_pagi'])): ?>
                                        <?php $fullInRaw = trim(($row['tanggal'] ?? '') . ' ' . ($row['daily_check_in_pagi'] ?? '')); $fullIn = sanitize(indo_datetime($fullInRaw)); ?>
                                        <span class="badge badge-success" data-toggle="tooltip" title="<?= $fullIn ?>"><?= sanitize($row['daily_check_in_pagi']) ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Belum</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['daily_check_out_sore'])): ?>
                                        <?php $fullOutRaw = trim(($row['tanggal'] ?? '') . ' ' . ($row['daily_check_out_sore'] ?? '')); $fullOut = sanitize(indo_datetime($fullOutRaw)); ?>
                                        <span class="badge badge-info" data-toggle="tooltip" title="<?= $fullOut ?>"><?= sanitize($row['daily_check_out_sore']) ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Belum</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= sanitize($row['jam_masuk'] ?? '-') ?></td>
                                <td><?= sanitize($row['jam_keluar'] ?? '-') ?></td>
                                <td><?= sanitize($row['catatan'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="9" class="text-center">Belum ada data absensi.</td>
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
        $('#tableAbsensiGuru').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/id.json'
            }
        });

        // Initialize Bootstrap tooltips for badges
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    });
</script>
