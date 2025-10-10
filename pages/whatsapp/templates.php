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
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Template WhatsApp</h6>
            <a href="<?= route('whatsapp_config', ['action' => 'edit_template']) ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Template
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="tableTemplates" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Nama Tampilan</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Aktif</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($templates as $index => $template): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= sanitize($template['name']) ?></td>
                                <td><?= sanitize($template['display_name']) ?></td>
                                <td><?= sanitize($template['category']) ?></td>
                                <td><?= sanitize($template['status']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $template['is_active'] ? 'success' : 'secondary' ?>">
                                        <?= $template['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td><?= indo_datetime($template['created_at'] ?? '') ?></td>
                                <td>
                                    <a href="<?= route('whatsapp_config', ['action' => 'edit_template', 'id' => $template['id']]) ?>" class="btn btn-sm btn-info">Edit</a>
                                    <form action="<?= route('whatsapp_config', ['action' => 'delete_template']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Hapus template ini?');">
                                        <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $template['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($templates)): ?>
                            <tr>
                                <td colspan="8" class="text-center">Belum ada template.</td>
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
        $('#tableTemplates').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/id.json'
            }
        });
    });
</script>
