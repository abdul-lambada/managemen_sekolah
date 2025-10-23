<div class="container-fluid">
    <?php if (!empty($alert)): ?>
        <div class="alert alert-<?= sanitize($alert['type']) ?> alert-dismissible fade show" role="alert">
            <?= $alert['message'] ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Guru</h6>
            <a href="<?= route('guru', ['action' => 'create']) ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Guru
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataGuru" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>
                                <i class="fas fa-user"></i> Nama
                            </th>
                            <th>
                                <i class="fas fa-id-card"></i> NIP
                            </th>
                            <th>
                                <i class="fas fa-venus-mars"></i> Gender
                            </th>
                            <th>
                                <i class="fas fa-birthday-cake"></i> Tanggal Lahir
                            </th>
                            <th>
                                <i class="fas fa-phone"></i> Telepon
                            </th>
                            <th>
                                <i class="fas fa-user-circle"></i> Akun
                            </th>
                            <th>
                                <i class="fas fa-cogs"></i> Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($guruList) || !isset($guruList)): ?>
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data guru.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($guruList as $index => $guru): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm mr-3">
                                                <div class="bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <?= strtoupper(substr($guru['nama_guru'] ?? 'N', 0, 1)) ?>
                                                </div>
                                            </div>
                                            <div>
                                                <strong><?= sanitize($guru['nama_guru'] ?? 'Unknown') ?></strong>
                                                <?php if (!empty($guru['user_id'])): ?>
                                                    <br><small class="text-success"><i class="fas fa-user-check"></i> Terhubung</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?= sanitize($guru['nip'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (($guru['jenis_kelamin'] ?? '') === 'Laki-laki'): ?>
                                            <span class="badge badge-info">
                                                <i class="fas fa-mars"></i> Laki-laki
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-pink">
                                                <i class="fas fa-venus"></i> Perempuan
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= !empty($guru['tanggal_lahir']) ? indo_date($guru['tanggal_lahir']) : '<span class="text-muted">-</span>' ?>
                                    </td>
                                    <td>
                                        <?= !empty($guru['phone']) ? '<span class="badge badge-light">' . sanitize($guru['phone']) . '</span>' : '<span class="text-muted">-</span>' ?>
                                    </td>
                                    <td>
                                        <?= !empty($guru['user_name']) ? '<span class="badge badge-success">' . sanitize($guru['user_name']) . '</span>' : '<span class="text-muted">Tidak terhubung</span>' ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <div class="btn-group" role="group">
                                            <a href="<?= route('guru', ['action' => 'show', 'id' => $guru['id_guru'] ?? 0]) ?>" class="btn btn-sm btn-secondary">Detail</a>
                                            <a href="<?= route('guru', ['action' => 'edit', 'id' => $guru['id_guru'] ?? 0]) ?>" class="btn btn-sm btn-info">Edit</a>
                                            <form action="<?= route('guru', ['action' => 'delete']) ?>" method="POST" class="d-inline" data-confirm="delete" data-confirm-message="Hapus data guru <?= sanitize($guru['nama_guru'] ?? '') ?>?">
                                                <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken ?? '') ?>">
                                                <input type="hidden" name="id" value="<?= (int) ($guru['id_guru'] ?? 0) ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        $('#dataGuru').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/id.json' }
        });
    });
</script>
