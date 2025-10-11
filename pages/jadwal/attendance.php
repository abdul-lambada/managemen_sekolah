<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Absensi Guru per Mata Pelajaran</h6>
        </div>
        <div class="card-body">
            <form class="form-inline mb-3" method="GET" action="<?= route('jadwal', ['action' => 'attendance']) ?>">
                <input type="hidden" name="page" value="jadwal">
                <input type="hidden" name="action" value="attendance">
                <div class="form-group mr-2">
                    <label for="filterStart" class="mr-2">Mulai</label>
                    <input type="date" class="form-control" id="filterStart" name="start" value="<?= sanitize($filters['start'] ?? '') ?>">
                </div>
                <div class="form-group mr-2">
                    <label for="filterEnd" class="mr-2">Akhir</label>
                    <input type="date" class="form-control" id="filterEnd" name="end" value="<?= sanitize($filters['end'] ?? '') ?>">
                </div>
                <div class="form-group mr-2">
                    <label for="filterKelasAbsensi" class="mr-2">Kelas</label>
                    <select class="form-control" id="filterKelasAbsensi" name="kelas">
                        <option value="">Semua</option>
                        <?php foreach ($kelasOptions as $kelas): ?>
                            <?php $kelasId = (int) $kelas['id_kelas']; ?>
                            <option value="<?= $kelasId ?>" <?= ($filters['kelas'] ?? null) === $kelasId ? 'selected' : '' ?>>
                                <?= sanitize(($kelas['nama_jurusan'] ?? '') . ' - ' . $kelas['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mr-2">
                    <label for="filterGuruAbsensi" class="mr-2">Guru</label>
                    <select class="form-control" id="filterGuruAbsensi" name="guru">
                        <option value="">Semua</option>
                        <?php foreach ($guruOptions as $guru): ?>
                            <?php $guruId = (int) $guru['id_guru']; ?>
                            <option value="<?= $guruId ?>" <?= ($filters['guru'] ?? null) === $guruId ? 'selected' : '' ?>>
                                <?= sanitize($guru['nama_guru']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mr-2">
                    <label for="filterMapelAbsensi" class="mr-2">Mapel</label>
                    <select class="form-control" id="filterMapelAbsensi" name="mapel">
                        <option value="">Semua</option>
                        <?php foreach ($mapelOptions as $mapel): ?>
                            <?php $mapelId = (int) $mapel['id_mata_pelajaran']; ?>
                            <option value="<?= $mapelId ?>" <?= ($filters['mapel'] ?? null) === $mapelId ? 'selected' : '' ?>>
                                <?= sanitize(($mapel['kode_mapel'] ?? '') . ' - ' . $mapel['nama_mapel']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mr-2">
                    <label for="filterStatus" class="mr-2">Status</label>
                    <select class="form-control" id="filterStatus" name="status">
                        <option value="">Semua</option>
                        <?php foreach ($statusOptions as $status): ?>
                            <option value="<?= sanitize($status) ?>" <?= ($filters['status'] ?? null) === $status ? 'selected' : '' ?>>
                                <?= sanitize($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mr-2">Filter</button>
                <a href="<?= route('jadwal', ['action' => 'attendance']) ?>" class="btn btn-light">Reset</a>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered" id="tableAbsensiMapel" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tanggal</th>
                            <th>Kelas</th>
                            <th>Mapel</th>
                            <th>Guru</th>
                            <th>Hari</th>
                            <th>Jam Mengajar</th>
                            <th>Status</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Sumber</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $index => $row): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= indo_date($row['tanggal']) ?></td>
                                <td><?= sanitize($row['nama_kelas']) ?></td>
                                <td><?= sanitize(($row['kode_mapel'] ?? '') . ' - ' . $row['nama_mapel']) ?></td>
                                <td><?= sanitize($row['nama_guru']) ?></td>
                                <td><?= sanitize($row['hari']) ?></td>
                                <td><?= sanitize(substr($row['jam_mulai'], 0, 5) . ' - ' . substr($row['jam_selesai'], 0, 5)) ?></td>
                                <td><?= attendance_badge($row['status_kehadiran']) ?></td>
                                <td><?= sanitize($row['jam_masuk'] ?? '-') ?></td>
                                <td><?= sanitize($row['jam_keluar'] ?? '-') ?></td>
                                <td><?= sanitize($row['sumber'] ?? '-') ?></td>
                                <td><?= sanitize($row['catatan'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="12" class="text-center">Belum ada data absensi mapel.</td>
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
        $('#tableAbsensiMapel').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/id.json'
            }
        });
    });
</script>
