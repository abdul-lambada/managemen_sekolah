<div class="container-fluid">
    <?php if (!empty($alert)): ?>
        <div class="alert alert-<?= sanitize($alert['type']) ?> alert-dismissible fade show" role="alert">
            <?= sanitize($alert['message']) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center justify-content-lg-between">
                <div class="d-flex align-items-center mb-3 mb-lg-0">
                    <h6 class="m-0 font-weight-bold text-primary mr-2">Jadwal Pelajaran</h6>
                    <?php if (has_role('admin')): ?>
                        <a href="<?= route('jadwal', ['action' => 'create']) ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Tambah Jadwal
                        </a>
                    <?php endif; ?>
                </div>

                <form class="w-100" method="GET" action="<?= route('jadwal') ?>">
                    <input type="hidden" name="page" value="jadwal">
                    <div class="form-row">
                        <div class="form-group col-12 col-md-6 col-lg-3">
                            <label for="filterKelas" class="small text-muted">Kelas</label>
                            <select class="form-control" id="filterKelas" name="kelas">
                                <option value="">Semua</option>
                                <?php foreach ($kelasOptions as $kelas): ?>
                                    <?php $kelasId = (int) $kelas['id_kelas']; ?>
                                    <option value="<?= $kelasId ?>" <?= ($filters['kelas'] ?? null) === $kelasId ? 'selected' : '' ?>>
                                        <?= sanitize(($kelas['nama_jurusan'] ?? '') . ' - ' . $kelas['nama_kelas']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3">
                            <label for="filterGuru" class="small text-muted">Guru</label>
                            <select class="form-control" id="filterGuru" name="guru">
                                <option value="">Semua</option>
                                <?php foreach ($guruOptions as $guru): ?>
                                    <?php $guruId = (int) $guru['id_guru']; ?>
                                    <option value="<?= $guruId ?>" <?= ($filters['guru'] ?? null) === $guruId ? 'selected' : '' ?>>
                                        <?= sanitize($guru['nama_guru']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-3">
                            <label for="filterMapel" class="small text-muted">Mapel</label>
                            <select class="form-control" id="filterMapel" name="mapel">
                                <option value="">Semua</option>
                                <?php foreach ($mapelOptions as $mapel): ?>
                                    <?php $mapelId = (int) $mapel['id_mata_pelajaran']; ?>
                                    <option value="<?= $mapelId ?>" <?= ($filters['mapel'] ?? null) === $mapelId ? 'selected' : '' ?>>
                                        <?= sanitize(($mapel['kode_mapel'] ?? '') . ' - ' . $mapel['nama_mapel']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-12 col-md-6 col-lg-2">
                            <label for="filterHari" class="small text-muted">Hari</label>
                            <select class="form-control" id="filterHari" name="hari">
                                <option value="">Semua</option>
                                <?php foreach ($hariOptions as $hari): ?>
                                    <option value="<?= sanitize($hari) ?>" <?= ($filters['hari'] ?? null) === $hari ? 'selected' : '' ?>>
                                        <?= sanitize($hari) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-12 col-lg-1 d-flex align-items-end">
                            <div class="w-100">
                                <button type="submit" class="btn btn-primary btn-block mb-2 mb-lg-0">Filter</button>
                            </div>
                        </div>
                        <div class="form-group col-12 col-lg-1 d-flex align-items-end">
                            <div class="w-100">
                                <a href="<?= route('jadwal') ?>" class="btn btn-light btn-block">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="tableJadwal" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kelas</th>
                            <th>Mapel</th>
                            <th>Guru</th>
                            <th>Hari</th>
                            <th>Jam</th>
                            <th>Ruang</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jadwalList as $index => $row): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= sanitize($row['nama_kelas']) ?></td>
                                <td><?= sanitize(($row['kode_mapel'] ?? '') . ' - ' . $row['nama_mapel']) ?></td>
                                <td><?= sanitize($row['nama_guru']) ?></td>
                                <td><?= sanitize($row['hari']) ?></td>
                                <td><?= sanitize(substr($row['jam_mulai'], 0, 5) . ' - ' . substr($row['jam_selesai'], 0, 5)) ?></td>
                                <td><?= sanitize($row['ruang'] ?? '-') ?></td>
                                <td><?= sanitize($row['catatan'] ?? '-') ?></td>
                                <td class="text-nowrap">
                                    <a href="<?= route('jadwal', ['action' => 'edit', 'id' => $row['id_jadwal']]) ?>" class="btn btn-sm btn-info">Edit</a>
                                    <form action="<?= route('jadwal', ['action' => 'delete']) ?>" method="POST" class="d-inline" data-confirm="delete" data-confirm-message="Hapus jadwal ini?">
                                        <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $row['id_jadwal'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($jadwalList)): ?>
                            <tr>
                                <td colspan="9" class="text-center">Belum ada jadwal.</td>
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
        $('#tableJadwal').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/id.json'
            }
        });
    });
</script>
