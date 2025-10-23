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
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>NIP</th>
                            <th>Jenis Kelamin</th>
                            <th>Tanggal Lahir</th>
                            <th>No. Telepon</th>
                            <th>Akun</th>
                            <th>Aksi</th>
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
                                    <td><?= sanitize($guru['nama_guru'] ?? '-') ?></td>
                                    <td><?= sanitize($guru['nip'] ?? '-') ?></td>
                                    <td><?= sanitize($guru['jenis_kelamin'] ?? '-') ?></td>
                                    <td><?= !empty($guru['tanggal_lahir']) ? sanitize(indo_date($guru['tanggal_lahir'])) : '-' ?></td>
                                    <td><?= sanitize($guru['phone'] ?? '-') ?></td>
                                    <td><?= !empty($guru['user_name']) ? sanitize($guru['user_name']) : 'Tidak terhubung' ?></td>
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
