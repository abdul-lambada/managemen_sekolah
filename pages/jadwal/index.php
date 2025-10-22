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
        <div class="card-header py-3 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
            <div class="d-flex align-items-center mb-2 mb-lg-0">
                <h6 class="m-0 font-weight-bold text-primary mr-2">Jadwal Pelajaran</h6>
                <?php if (has_role('admin')): ?>
                    <a href="<?= route('jadwal', ['action' => 'create']) ?>" class="btn btn-sm btn-primary ml-1">
                        <i class="fas fa-plus"></i> Tambah Jadwal
                    </a>
                <?php endif; ?>
            </div>
            <form class="form-inline mt-3 mt-lg-0" method="GET" action="<?= route('jadwal') ?>">
                <input type="hidden" name="page" value="jadwal">

                <div class="form-group mr-2">
                    <label for="filterKelas" class="mr-2">Kelas</label>
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

                <div class="form-group mr-2">
                    <label for="filterGuru" class="mr-2">Guru</label>
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

                <div class="form-group mr-2">
                    <label for="filterMapel" class="mr-2">Mapel</label>
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

                <div class="form-group mr-2">
                    <label for="filterHari" class="mr-2">Hari</label>
                    <select class="form-control" id="filterHari" name="hari">
                        <option value="">Semua</option>
                        <?php foreach ($hariOptions as $hari): ?>
                            <option value="<?= sanitize($hari) ?>" <?= ($filters['hari'] ?? null) === $hari ? 'selected' : '' ?>>
                                <?= sanitize($hari) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary mr-2">Filter</button>
                <a href="<?= route('jadwal') ?>" class="btn btn-light">Reset</a>
            </form>
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
