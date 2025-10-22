<div class="container-fluid">
    <?php if (!empty($alert)): ?>
        <div class="alert alert-<?= sanitize($alert['type']) ?> alert-dismissible fade show" role="alert">
            <?= sanitize($alert['message']) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Perangkat</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format(count($devices)) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-fingerprint fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Aktif</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($activeCount ?? 0) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <div class="d-flex align-items-center flex-wrap">
                <h6 class="m-0 font-weight-bold text-primary mr-3">Daftar Perangkat</h6>
                <?php if (!empty($lastSyncSummary['last_sync_at'])): ?>
                    <span class="badge badge-light border mr-2">
                        Last Sync: <?= indo_datetime($lastSyncSummary['last_sync_at']) ?>
                    </span>
                    <span class="badge badge-primary mr-2">
                        <?= (int)($lastSyncSummary['connected_devices'] ?? 0) ?>/<?= number_format($activeCount ?? 0) ?> device
                    </span>
                    <span class="badge badge-info">
                        <?= number_format((int)($lastSyncSummary['total_logs'] ?? 0)) ?> log
                    </span>
                <?php else: ?>
                    <span class="badge badge-secondary">Belum ada sinkronisasi terbaru</span>
                <?php endif; ?>
            </div>
            <div class="d-flex align-items-center mt-3 mt-md-0">
                <a href="<?= route('fingerprint_devices', ['action' => 'create']) ?>" class="btn btn-sm btn-primary mr-2">
                    <i class="fas fa-plus"></i> Tambah Perangkat
                </a>
                <a href="<?= route('fingerprint_devices', ['action' => 'sync']) ?>" class="btn btn-sm btn-success mr-2">
                    <i class="fas fa-sync"></i> Sinkronkan
                </a>
                <a href="<?= route('fingerprint_devices', ['action' => 'uids']) ?>" class="btn btn-sm btn-warning mr-2">
                    <i class="fas fa-id-badge"></i> Mapping UID
                </a>
                <a href="<?= route('fingerprint_devices', ['action' => 'uids_siswa']) ?>" class="btn btn-sm btn-warning mr-2">
                    <i class="fas fa-id-card"></i> Mapping UID Siswa
                </a>
                <a href="<?= route('fingerprint_devices', ['action' => 'logs']) ?>" class="btn btn-sm btn-info">
                    <i class="fas fa-clipboard-list"></i> Lihat Log
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="tableDevices" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>IP Address</th>
                            <th>Port</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Terakhir Sinkronisasi</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($devices as $index => $device): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= sanitize($device['ip']) ?></td>
                                <td><?= sanitize($device['port']) ?></td>
                                <td><?= sanitize($device['nama_lokasi']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $device['is_active'] ? 'success' : 'secondary' ?>">
                                        <?= $device['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php $ls = $device['last_sync_at'] ?? ($lastSyncMap[$device['ip']] ?? null); ?>
                                    <?= $ls ? indo_datetime($ls) : '-' ?>
                                </td>
                                <td><?= indo_datetime($device['created_at'] ?? '') ?></td>
                                <td>
                                    <a href="<?= route('fingerprint_devices', ['action' => 'edit', 'id' => $device['id']]) ?>" class="btn btn-sm btn-info">Edit</a>
                                    <form action="<?= route('fingerprint_devices', ['action' => 'delete']) ?>" method="POST" class="d-inline" data-confirm="delete" data-confirm-message="Hapus perangkat di lokasi <?= sanitize($device['nama_lokasi']) ?>?">
                                        <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $device['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($devices)): ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada perangkat.</td>
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
        $('#tableDevices').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.1/i18n/id.json'
            }
        });
    });
</script>
