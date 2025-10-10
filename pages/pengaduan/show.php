<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Detail Pengaduan</h6>
            <a href="<?= route('pengaduan') ?>" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Nama Pelapor</dt>
                <dd class="col-sm-9"><?= sanitize($pengaduan['nama_pelapor']) ?></dd>

                <dt class="col-sm-3">Peran</dt>
                <dd class="col-sm-9"><?= sanitize(ucfirst($pengaduan['role_pelapor'])) ?></dd>

                <dt class="col-sm-3">Kontak</dt>
                <dd class="col-sm-9">
                    <?php if (!empty($pengaduan['no_wa'])): ?>
                        <div>WA: <?= sanitize($pengaduan['no_wa']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($pengaduan['email_pelapor'])): ?>
                        <div>Email: <?= sanitize($pengaduan['email_pelapor']) ?></div>
                    <?php endif; ?>
                </dd>

                <dt class="col-sm-3">Kategori</dt>
                <dd class="col-sm-9"><?= sanitize($pengaduan['kategori']) ?></dd>

                <dt class="col-sm-3">Judul</dt>
                <dd class="col-sm-9"><?= sanitize($pengaduan['judul_pengaduan']) ?></dd>

                <dt class="col-sm-3">Isi Pengaduan</dt>
                <dd class="col-sm-9"><?= nl2br(sanitize($pengaduan['isi_pengaduan'])) ?></dd>

                <?php if (!empty($pengaduan['keterangan'])): ?>
                    <dt class="col-sm-3">Keterangan Tambahan</dt>
                    <dd class="col-sm-9"><?= nl2br(sanitize($pengaduan['keterangan'])) ?></dd>
                <?php endif; ?>

                <?php if (!empty($pengaduan['file_pendukung'])): ?>
                    <dt class="col-sm-3">File Pendukung</dt>
                    <dd class="col-sm-9">
                        <a href="<?= uploads_url($pengaduan['file_pendukung']) ?>" target="_blank" rel="noopener">Lihat File</a>
                    </dd>
                <?php endif; ?>

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    <form action="<?= route('pengaduan', ['action' => 'update_status']) ?>" method="POST" class="form-inline">
                        <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                        <input type="hidden" name="id" value="<?= (int) $pengaduan['id_pengaduan'] ?>">
                        <input type="hidden" name="redirect" value="<?= route('pengaduan', ['action' => 'show', 'id' => $pengaduan['id_pengaduan']]) ?>">
                        <select name="status" class="custom-select custom-select-sm mr-2">
                            <option value="pending" <?= $pengaduan['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="diproses" <?= $pengaduan['status'] === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                            <option value="selesai" <?= $pengaduan['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                    </form>
                </dd>

                <dt class="col-sm-3">Tanggal Pengaduan</dt>
                <dd class="col-sm-9"><?= sanitize($pengaduan['tanggal_pengaduan']) ?></dd>
            </dl>
        </div>
    </div>
</div>