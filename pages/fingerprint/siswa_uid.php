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
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Mapping UID Fingerprint ke Siswa</h6>
            <a href="<?= route('fingerprint_devices') ?>" class="btn btn-sm btn-light"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= route('fingerprint_devices', ['action' => 'store_uid_siswa']) ?>" class="mb-4">
                <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="id_siswa">Siswa</label>
                        <select class="form-control" id="id_siswa" name="id_siswa" required>
                            <option value="">-- Pilih Siswa --</option>
                            <?php foreach ($siswas as $s): ?>
                                <option value="<?= (int)$s['id_siswa'] ?>"><?= sanitize($s['nama_siswa']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="fingerprint_uid">UID Fingerprint</label>
                        <?php if (!empty($uidOptions)): ?>
                            <select class="form-control" id="fingerprint_uid" name="fingerprint_uid" required>
                                <option value="">-- Pilih UID --</option>
                                <?php foreach ($uidOptions as $uid): ?>
                                    <option value="<?= sanitize($uid) ?>"><?= sanitize($uid) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Daftar UID diambil dari log fingerprint terbaru.</small>
                        <?php else: ?>
                            <input type="text" class="form-control" id="fingerprint_uid" name="fingerprint_uid" placeholder="Contoh: 2001" required>
                        <?php endif; ?>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="device_serial">Serial Perangkat (opsional)</label>
                        <input type="text" class="form-control" id="device_serial" name="device_serial" placeholder="SN perangkat">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Mapping</button>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered" id="tableUidMapSiswa" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Siswa</th>
                            <th>UID</th>
                            <th>Serial</th>
                            <th>Dibuat</th>
                            <th>Diperbarui</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mappings as $i => $m): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= sanitize($m['nama_siswa']) ?></td>
                                <td><?= sanitize($m['fingerprint_uid']) ?></td>
                                <td><?= sanitize($m['device_serial'] ?? '-') ?></td>
                                <td><?= indo_datetime($m['created_at'] ?? '') ?></td>
                                <td><?= indo_datetime($m['updated_at'] ?? '') ?></td>
                                <td>
                                    <form action="<?= route('fingerprint_devices', ['action' => 'delete_uid_siswa']) ?>" method="POST" class="d-inline" data-confirm="delete" data-confirm-message="Hapus mapping UID siswa ini?">
                                        <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                                        <input type="hidden" name="id_siswa" value="<?= (int)$m['id_siswa'] ?>">
                                        <input type="hidden" name="fingerprint_uid" value="<?= sanitize($m['fingerprint_uid']) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($mappings)): ?>
                            <tr><td colspan="7" class="text-center">Belum ada mapping UID siswa.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        $('#tableUidMapSiswa').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/id.json' }
        });
    });
</script>
