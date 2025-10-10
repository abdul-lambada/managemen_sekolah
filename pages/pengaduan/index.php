<?php

declare(strict_types=1);

$pengaduan = $pengaduan ?? [];
?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daftar Pengaduan</h1>
        <p class="text-muted mb-0">Tinjau dan tindak lanjuti pengaduan yang masuk.</p>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Ringkasan Pengaduan</h6>
        </div>
        <div class="card-body">
            <?php if (empty($pengaduan)): ?>
                <div class="alert alert-info mb-0">
                    Belum ada pengaduan.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pelapor</th>
                            <th>Kontak</th>
                            <th>Kategori</th>
                            <th>Judul</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($pengaduan as $item): ?>
                            <tr>
                                <td><?= sanitize($item['tanggal_pengaduan']) ?></td>
                                <td>
                                    <?= sanitize($item['nama_pelapor']) ?>
                                    <div class="small text-muted">Peran: <?= sanitize($item['role_pelapor']) ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($item['no_wa'])): ?>
                                        <div>WA: <?= sanitize($item['no_wa']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($item['email_pelapor'])): ?>
                                        <div>Email: <?= sanitize($item['email_pelapor']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= sanitize($item['kategori']) ?></td>
                                <td>
                                    <strong><?= sanitize($item['judul_pengaduan']) ?></strong>
                                    <div class="small text-muted"><?= sanitize(mb_strimwidth($item['isi_pengaduan'], 0, 80, '...')) ?></div>
                                </td>
                                <td>
                                    <span class="badge badge-<?= match ($item['status']) {
                                        'pending' => 'warning',
                                        'diproses' => 'info',
                                        'selesai' => 'success',
                                        default => 'secondary'
                                    } ?>">
                                        <?= sanitize(ucfirst($item['status'])) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
