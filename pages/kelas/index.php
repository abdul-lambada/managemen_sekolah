<div class="container-fluid">
    <?php if (!empty($alert)): ?>
        <div class="alert alert-<?= sanitize($alert['type']) ?> alert-dismissible fade show" role="alert">
            <?= sanitize($alert['message']) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Kelas</h6>
            <a href="<?= route('kelas', ['action' => 'create']) ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Kelas
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataKelas" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Kelas</th>
                            <th>Jurusan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kelasList as $index => $kelas): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= sanitize($kelas['nama_kelas']) ?></td>
                                <td><?= sanitize($kelas['nama_jurusan']) ?></td>
                                <td class="text-nowrap">
                                    <a href="<?= route('kelas', ['action' => 'show', 'id' => $kelas['id_kelas']]) ?>" class="btn btn-sm btn-secondary">Detail</a>
                                    <a href="<?= route('kelas', ['action' => 'edit', 'id' => $kelas['id_kelas']]) ?>" class="btn btn-sm btn-info">Edit</a>
                                    <form action="<?= route('kelas', ['action' => 'delete']) ?>" method="POST" class="d-inline" data-confirm="delete" data-confirm-message="Hapus kelas <?= sanitize($kelas['nama_kelas']) ?>?">
                                        <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $kelas['id_kelas'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($kelasList)): ?>
                            <tr>
                                <td colspan="4" class="text-center">Belum ada data kelas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        $('#dataKelas').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/id.json'
            }
        });
    });
</script>
