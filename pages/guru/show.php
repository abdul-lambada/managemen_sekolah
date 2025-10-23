<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Detail Guru</h6>
            <a href="<?= route('guru') ?>" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nama</dt>
                        <dd class="col-sm-8"><?= sanitize($guru['nama_guru']) ?></dd>

                        <dt class="col-sm-4">NIP</dt>
                        <dd class="col-sm-8"><?= sanitize($guru['nip']) ?></dd>

                        <dt class="col-sm-4">Jenis Kelamin</dt>
                        <dd class="col-sm-8"><?= sanitize($guru['jenis_kelamin']) ?></dd>

                        <dt class="col-sm-4">Tanggal Lahir</dt>
                        <dd class="col-sm-8"><?= $guru['tanggal_lahir'] ? indo_date($guru['tanggal_lahir']) : '-' ?></dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">No. Telepon</dt>
                        <dd class="col-sm-8"><?= sanitize($guru['phone'] ?? '-') ?></dd>

                        <dt class="col-sm-4">Alamat</dt>
                        <dd class="col-sm-8"><?= nl2br(sanitize($guru['alamat'] ?? '-')) ?></dd>

                        <dt class="col-sm-4">Akun Pengguna</dt>
                        <dd class="col-sm-8"><?= !empty($guru['user_name']) ? sanitize($guru['user_name']) : '<span class="text-muted">Tidak terhubung</span>' ?></dd>
                    </dl>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Dibuat</dt>
                        <dd class="col-sm-8"><?= !empty($guru['created_at']) ? indo_datetime($guru['created_at']) : '-' ?></dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Diperbarui</dt>
                        <dd class="col-sm-8"><?= !empty($guru['updated_at']) ? indo_datetime($guru['updated_at']) : '-' ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
