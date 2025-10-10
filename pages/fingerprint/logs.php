<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Log Fingerprint</h6>
            <form class="form-inline" method="GET" action="<?= route('fingerprint_logs') ?>">
                <input type="hidden" name="page" value="fingerprint_logs">
                <label class="mr-2" for="limit">Jumlah</label>
                <select class="form-control mr-2" id="limit" name="limit">
                    <?php foreach ([50, 100, 250, 500] as $opt): ?>
                        <option value="<?= $opt ?>" <?= ($limit ?? 100) == $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">
                    Terapkan
                </button>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="tableFingerprintLogs" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Aksi</th>
                            <th>Status</th>
                            <th>Pesan</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $index => $log): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= sanitize($log['action']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $log['status'] === 'success' ? 'success' : ($log['status'] === 'warning' ? 'warning' : 'danger') ?>">
                                        <?= sanitize($log['status']) ?>
                                    </span>
                                </td>
                                <td><?= sanitize($log['message']) ?></td>
                                <td><?= indo_datetime($log['created_at'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Belum ada log.</td>
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
        $('#tableFingerprintLogs').DataTable({
            order: [[4, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/id.json'
            }
        });
    });
</script>
