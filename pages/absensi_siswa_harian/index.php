<?php
// Guards to avoid undefined variable notices
$records = is_array($records ?? null) ? $records : [];
$kelasOptions = is_array($kelasOptions ?? null) ? $kelasOptions : [];
$start = $start ?? '';
$end = $end ?? '';
$kelasId = $kelasId ?? '';
?>
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Rekap Harian Siswa</h6>
        </div>
        <div class="card-body">
            <form class="form-inline mb-3" method="GET" action="<?= route('absensi_siswa_harian') ?>">
                <input type="hidden" name="page" value="absensi_siswa_harian">
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
                        <option value="">-- Semua Kelas --</option>
                        <?php foreach ($kelasOptions as $k): ?>
                            <option value="<?= (int) $k['id_kelas'] ?>" <?= ((int)($kelasId ?? 0)) === (int)$k['id_kelas'] ? 'selected' : '' ?>>
                                <?= sanitize(($k['nama_jurusan'] ?? '') . ' - ' . ($k['nama_kelas'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mr-2">Filter</button>
                <a href="<?= route('absensi_siswa_harian') ?>" class="btn btn-light mr-2">Reset</a>
                <a href="<?= route('absensi_siswa_harian', ['start' => $start, 'end' => $end, 'kelas' => $kelasId, 'export' => 'csv']) ?>" class="btn btn-success" target="_blank">
                    <i class="fas fa-file-csv"></i> CSV
                </a>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered" id="tableAbsensiSiswaHarian" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tanggal</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Check-in Pagi</th>
                            <th>Check-out Sore</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $index => $row): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= indo_date($row['tanggal']) ?></td>
                                <td><?= sanitize($row['nama_siswa']) ?> <small class="text-muted d-block">NIS: <?= sanitize($row['nis']) ?> | NISN: <?= sanitize($row['nisn']) ?></small></td>
                                <td><?= sanitize(($row['nama_jurusan'] ?? '') . ' - ' . ($row['nama_kelas'] ?? '')) ?></td>
                                <td>
                                    <?php if (!empty($row['check_in_pagi'])): ?>
                                        <?php $inRaw = trim(($row['tanggal'] ?? '') . ' ' . ($row['check_in_pagi'] ?? '')); $inTitle = sanitize(indo_datetime($inRaw)); ?>
                                        <span class="badge badge-success" data-toggle="tooltip" title="<?= $inTitle ?>"><?= sanitize($row['check_in_pagi']) ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Belum</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['check_out_sore'])): ?>
                                        <?php $outRaw = trim(($row['tanggal'] ?? '') . ' ' . ($row['check_out_sore'] ?? '')); $outTitle = sanitize(indo_datetime($outRaw)); ?>
                                        <span class="badge badge-info" data-toggle="tooltip" title="<?= $outTitle ?>"><?= sanitize($row['check_out_sore']) ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Belum</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.jQuery && jQuery.fn && typeof jQuery.fn.DataTable === 'function') {
            jQuery('#tableAbsensiSiswaHarian').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/id.json' }
            });
        }
        if (window.jQuery && jQuery.fn && jQuery.fn.tooltip) {
            jQuery(function(){ jQuery('[data-toggle="tooltip"]').tooltip(); });
        }
    });
</script>
