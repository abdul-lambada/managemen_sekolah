<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Detail Kelas</h6>
            <div>
                <a href="<?= route('kelas') ?>" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <a href="<?= route('kelas', ['action' => 'edit', 'id' => $kelas['id_kelas']]) ?>" class="btn btn-info btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nama Kelas</dt>
                        <dd class="col-sm-8"><?= sanitize($kelas['nama_kelas']) ?></dd>

                        <dt class="col-sm-4">Jurusan</dt>
                        <dd class="col-sm-8"><?= sanitize($kelas['nama_jurusan']) ?></dd>

                        <dt class="col-sm-4">Dibuat</dt>
                        <dd class="col-sm-8"><?= !empty($kelas['created_at']) ? indo_datetime($kelas['created_at']) : '-' ?></dd>

                        <dt class="col-sm-4">Diperbarui</dt>
                        <dd class="col-sm-8"><?= !empty($kelas['updated_at']) ? indo_datetime($kelas['updated_at']) : '-' ?></dd>
                    </dl>
                </div>
            </div>
            <hr>
            <h6 class="font-weight-bold text-primary">Daftar Siswa</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>NISN</th>
                            <th>NIS</th>
                            <th>Jenis Kelamin</th>
                            <th>No. Telepon</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $index => $student): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= sanitize($student['nama_siswa']) ?></td>
                                <td><?= sanitize($student['nisn']) ?></td>
                                <td><?= sanitize($student['nis']) ?></td>
                                <td><?= sanitize($student['jenis_kelamin']) ?></td>
                                <td><?= sanitize($student['phone'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Belum ada siswa dalam kelas ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
