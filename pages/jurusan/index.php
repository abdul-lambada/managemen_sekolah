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
            <h6 class="m-0 font-weight-bold text-primary">Daftar Jurusan</h6>
            <a href="<?= route('jurusan', ['action' => 'create']) ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Jurusan
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataJurusan" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Jurusan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jurusanList as $index => $jurusan): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= sanitize($jurusan['nama_jurusan']) ?></td>
                                <td class="text-nowrap">
                                    <a href="<?= route('jurusan', ['action' => 'show', 'id' => $jurusan['id_jurusan']]) ?>" class="btn btn-sm btn-secondary">Detail</a>
                                    <a href="<?= route('jurusan', ['action' => 'edit', 'id' => $jurusan['id_jurusan']]) ?>" class="btn btn-sm btn-info">Edit</a>
                                    <form action="<?= route('jurusan', ['action' => 'delete']) ?>" method="POST" class="d-inline" data-confirm="delete" data-confirm-message="Hapus jurusan <?= sanitize($jurusan['nama_jurusan']) ?>?">
                                        <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $jurusan['id_jurusan'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($jurusanList)): ?>
                            <tr>
                                <td colspan="3" class="text-center">Belum ada data jurusan.</td>
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
        $('#dataJurusan').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/id.json'
            }
        });
    });
</script>
