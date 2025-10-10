<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Detail Siswa</h6>
            <a href="<?= route('siswa') ?>" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nama</dt>
                        <dd class="col-sm-8"><?= sanitize($student['nama_siswa']) ?></dd>

                        <dt class="col-sm-4">NISN</dt>
                        <dd class="col-sm-8"><?= sanitize($student['nisn']) ?></dd>

                        <dt class="col-sm-4">NIS</dt>
                        <dd class="col-sm-8"><?= sanitize($student['nis']) ?></dd>

                        <dt class="col-sm-4">Jenis Kelamin</dt>
                        <dd class="col-sm-8"><?= sanitize($student['jenis_kelamin']) ?></dd>

                        <dt class="col-sm-4">Tanggal Lahir</dt>
                        <dd class="col-sm-8"><?= $student['tanggal_lahir'] ? indo_date($student['tanggal_lahir']) : '-' ?></dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Kelas</dt>
                        <dd class="col-sm-8"><?= sanitize($student['nama_kelas'] ?? '-') ?></dd>

                        <dt class="col-sm-4">Jurusan</dt>
                        <dd class="col-sm-8"><?= sanitize($student['nama_jurusan'] ?? '-') ?></dd>

                        <dt class="col-sm-4">No. Telepon</dt>
                        <dd class="col-sm-8"><?= sanitize($student['phone'] ?? '-') ?></dd>

                        <dt class="col-sm-4">Alamat</dt>
                        <dd class="col-sm-8"><?= nl2br(sanitize($student['alamat'] ?? '-')) ?></dd>

                        <dt class="col-sm-4">Akun Pengguna</dt>
                        <dd class="col-sm-8">
                            <?= !empty($student['user_name']) ? sanitize($student['user_name']) : '<span class="text-muted">Tidak terhubung</span>' ?>
                        </dd>
                    </dl>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Dibuat</dt>
                        <dd class="col-sm-8"><?= !empty($student['created_at']) ? indo_datetime($student['created_at']) : '-' ?></dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Diperbarui</dt>
                        <dd class="col-sm-8"><?= !empty($student['updated_at']) ? indo_datetime($student['updated_at']) : '-' ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
