<div class="container-fluid">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= sanitize($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <?= $isEdit ? 'Edit Perangkat Fingerprint' : 'Tambah Perangkat Fingerprint' ?>
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= route('fingerprint_devices', ['action' => $isEdit ? 'update' : 'store']) ?>">
                <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= (int) ($device['id'] ?? 0) ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="ip">IP Address</label>
                    <input type="text" class="form-control" id="ip" name="ip"
                           value="<?= sanitize($device['ip'] ?? '') ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="port">Port</label>
                        <input type="number" class="form-control" id="port" name="port"
                               value="<?= sanitize($device['port'] ?? 4370) ?>" min="1" max="65535" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="nama_lokasi">Nama Lokasi</label>
                        <input type="text" class="form-control" id="nama_lokasi" name="nama_lokasi"
                               value="<?= sanitize($device['nama_lokasi'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?= sanitize($device['keterangan'] ?? '') ?></textarea>
                </div>

                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                        <?= ($device['is_active'] ?? 1) ? 'checked' : '' ?> >
                    <label class="form-check-label" for="is_active">Aktif</label>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="<?= route('fingerprint_devices') ?>" class="btn btn-light"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <button type="submit" class="btn btn-primary">
                        <?= $isEdit ? 'Perbarui' : 'Simpan' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
