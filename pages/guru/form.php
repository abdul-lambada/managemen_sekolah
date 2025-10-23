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
                <?= $isEdit ? 'Edit Guru' : 'Tambah Guru' ?>
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= route('guru', ['action' => $isEdit ? 'update' : 'store']) ?>">
                <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= (int) ($guru['id_guru'] ?? 0) ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="nama_guru">Nama Guru</label>
                    <input type="text" class="form-control" id="nama_guru" name="nama_guru"
                           value="<?= sanitize($guru['nama_guru'] ?? '') ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="nip">NIP</label>
                        <input type="text" class="form-control" id="nip" name="nip"
                               value="<?= sanitize($guru['nip'] ?? '') ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="jenis_kelamin">Jenis Kelamin</label>
                        <select class="form-control" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">-- Pilih --</option>
                            <option value="Laki-laki" <?= ($guru['jenis_kelamin'] ?? '') === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="Perempuan" <?= ($guru['jenis_kelamin'] ?? '') === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="tanggal_lahir">Tanggal Lahir</label>
                        <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir"
                               value="<?= sanitize($guru['tanggal_lahir'] ?? '') ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="phone">No. Telepon</label>
                        <input type="text" class="form-control" id="phone" name="phone"
                               value="<?= sanitize($guru['phone'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?= sanitize($guru['alamat'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="user_id">Akun Pengguna</label>
                    <select class="form-control" id="user_id" name="user_id">
                        <option value="">-- Tidak terhubung --</option>
                        <?php foreach ($userOptions as $user): ?>
                            <option value="<?= (int) $user['id'] ?>" <?= ((int) ($guru['user_id'] ?? 0)) === (int) $user['id'] ? 'selected' : '' ?>>
                                <?= sanitize($user['name']) ?> (<?= sanitize($user['role']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="<?= route('guru') ?>" class="btn btn-light"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <button type="submit" class="btn btn-primary">
                        <?= $isEdit ? 'Perbarui' : 'Simpan' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
