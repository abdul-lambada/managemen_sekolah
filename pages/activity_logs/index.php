<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Log Aktivitas</h6>
            <form class="form-inline" method="GET" action="<?= route('activity_logs') ?>">
                <input type="hidden" name="page" value="activity_logs">
                <div class="form-group mr-2 mb-2 mb-md-0">
                    <label for="action_filter" class="mr-2">Aksi</label>
                    <input type="text" class="form-control" id="action_filter" name="action_filter" value="<?= sanitize($actionFilter ?? '') ?>" placeholder="auth.login">
                </div>
                <div class="form-group mr-2 mb-2 mb-md-0">
                    <label for="user_id" class="mr-2">User ID</label>
                    <input type="number" class="form-control" id="user_id" name="user_id" value="<?= sanitize((string) ($userId ?? '')) ?>" min="1">
                </div>
                <div class="form-group mr-2 mb-2 mb-md-0">
                    <label for="limit" class="mr-2">Limit</label>
                    <select class="form-control" id="limit" name="limit">
                        <?php foreach ([25, 50, 100, 200] as $limitOption): ?>
                            <option value="<?= $limitOption ?>" <?= ((int) ($limit ?? 100) === $limitOption) ? 'selected' : '' ?>><?= $limitOption ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="tableActivityLogs" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>User ID</th>
                            <th>Nama</th>
                            <th>Aksi</th>
                            <th>Deskripsi</th>
                            <th>IP Address</th>
                            <th>User Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= indo_datetime($log['created_at']) ?></td>
                                <td><?= sanitize((string) ($log['user_id'] ?? '-')) ?></td>
                                <td><?= sanitize($log['user_name'] ?? '-') ?></td>
                                <td><span class="badge badge-info"><?= sanitize($log['action']) ?></span></td>
                                <td><?= sanitize($log['description'] ?? '-') ?></td>
                                <td><?= sanitize($log['ip_address'] ?? '-') ?></td>
                                <td class="small"><?= sanitize($log['user_agent'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data log.</td>
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
        $('#tableActivityLogs').DataTable({
            order: [[0, 'desc']],
            pageLength: 25,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/id.json'
            }
        });
    });
</script>
