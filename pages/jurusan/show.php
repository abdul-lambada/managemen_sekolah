<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Detail Jurusan</h6>
            <div>
                <a href="<?= route('jurusan') ?>" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <a href="<?= route('jurusan', ['action' => 'edit', 'id' => $jurusan['id_jurusan']]) ?>" class="btn btn-info btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nama Jurusan</dt>
                        <dd class="col-sm-8"><?= sanitize($jurusan['nama_jurusan']) ?></dd>

                        <dt class="col-sm-4">Dibuat</dt>
                        <dd class="col-sm-8"><?= !empty($jurusan['created_at']) ? indo_datetime($jurusan['created_at']) : '-' ?></dd>

                        <dt class="col-sm-4">Diperbarui</dt>
                        <dd class="col-sm-8"><?= !empty($jurusan['updated_at']) ? indo_datetime($jurusan['updated_at']) : '-' ?></dd>
                    </dl>
                </div>
            </div>
            <hr>
            <h6 class="font-weight-bold text-primary">Daftar Kelas</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Kelas</th>
                            <th>Dibuat</th>
                            <th>Diperbarui</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kelasList as $index => $kelas): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= sanitize($kelas['nama_kelas']) ?></td>
                                <td><?= !empty($kelas['created_at']) ? indo_datetime($kelas['created_at']) : '-' ?></td>
                                <td><?= !empty($kelas['updated_at']) ? indo_datetime($kelas['updated_at']) : '-' ?></td>
                                <td class="text-nowrap">
                                    <a href="<?= route('kelas', ['action' => 'show', 'id' => $kelas['id_kelas']]) ?>" class="btn btn-sm btn-secondary">Detail Kelas</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($kelasList)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Belum ada kelas pada jurusan ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
