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
                <?= $isEdit ? 'Edit Kelas' : 'Tambah Kelas' ?>
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= route('kelas', ['action' => $isEdit ? 'update' : 'store']) ?>">
                <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= (int) ($kelas['id_kelas'] ?? 0) ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="nama_kelas">Nama Kelas</label>
                    <input type="text" class="form-control" id="nama_kelas" name="nama_kelas"
                           value="<?= sanitize($kelas['nama_kelas'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="id_jurusan">Jurusan</label>
                    <select class="form-control" id="id_jurusan" name="id_jurusan" required>
                        <option value="">-- Pilih Jurusan --</option>
                        <?php foreach ($jurusanOptions as $jurusan): ?>
                            <option value="<?= (int) $jurusan['id_jurusan'] ?>"
                                <?= ((int) ($kelas['id_jurusan'] ?? 0)) === (int) $jurusan['id_jurusan'] ? 'selected' : '' ?>>
                                <?= sanitize($jurusan['nama_jurusan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="<?= route('kelas') ?>" class="btn btn-light"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <button type="submit" class="btn btn-primary">
                        <?= $isEdit ? 'Perbarui' : 'Simpan' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
