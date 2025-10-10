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
                <?= $isEdit ? 'Edit Jurusan' : 'Tambah Jurusan' ?>
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= route('jurusan', ['action' => $isEdit ? 'update' : 'store']) ?>">
                <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= (int) ($jurusan['id_jurusan'] ?? 0) ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="nama_jurusan">Nama Jurusan</label>
                    <input type="text" class="form-control" id="nama_jurusan" name="nama_jurusan"
                           value="<?= sanitize($jurusan['nama_jurusan'] ?? '') ?>" required>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="<?= route('jurusan') ?>" class="btn btn-light"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <button type="submit" class="btn btn-primary">
                        <?= $isEdit ? 'Perbarui' : 'Simpan' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
