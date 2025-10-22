<div class="container-fluid">
    <?php if (!empty($alert)): ?>
        <div class="alert alert-<?= sanitize($alert['type']) ?> alert-dismissible fade show" role="alert">
            <?= sanitize($alert['message']) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Automasi</h1>
    </div>

    <div class="row">
        <?php foreach ([
            'whatsapp' => ['title' => 'Dispatch WhatsApp', 'icon' => 'fa-paper-plane', 'action' => 'whatsapp'],
            'fingerprint' => ['title' => 'Sync Fingerprint', 'icon' => 'fa-fingerprint', 'action' => 'fingerprint'],
        ] as $key => $config): ?>
            <?php $log = $logs[$key] ?? ['badge' => 'secondary', 'label' => 'Belum ada data', 'meta' => []]; ?>
            <div class="col-xl-6 col-md-12 mb-4">
                <div class="card border-left-<?= sanitize($log['badge']) ?> shadow h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="font-weight-bold text-<?= sanitize($log['badge']) ?> mb-1">
                                    <i class="fas <?= $config['icon'] ?>"></i> <?= sanitize($config['title']) ?>
                                </h5>
                                <div class="mb-2">Status: <span class="badge badge-<?= sanitize($log['badge']) ?>">
                                    <?= sanitize($log['label']) ?></span>
                                </div>
                                <?php if (!empty($log['meta']['timestamp'])): ?>
                                    <div class="text-muted small mb-2">
                                        Terakhir: <?= indo_datetime($log['meta']['timestamp']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($log['meta'])): ?>
                                    <?php if ($key === 'fingerprint'): ?>
                                        <?php $m = $log['meta']; ?>
                                        <?php if (!empty($m['message'])): ?>
                                            <div class="alert alert-<?= ($log['badge'] ?? 'secondary') === 'success' ? 'success' : (($log['badge'] ?? '') === 'warning' ? 'warning' : 'danger') ?> py-1 mb-2 small">
                                                <?= sanitize($m['message']) ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="small mb-2">
                                            <div>Processed: <strong><?= (int)($m['processed'] ?? ($m['schedule_processed'] ?? 0)) ?></strong></div>
                                            <div>
                                                Sukses: <span class="badge badge-success"><?= (int)($m['total_success'] ?? 0) ?></span>
                                                Gagal: <span class="badge badge-danger"><?= (int)($m['total_failure'] ?? 0) ?></span>
                                                Peringatan: <span class="badge badge-warning"><?= (int)($m['total_warning'] ?? 0) ?></span>
                                                Terlambat: <span class="badge badge-info"><?= (int)($m['late_notifications'] ?? 0) ?></span>
                                            </div>
                                        </div>

                                        <?php if (!empty($m['devices']) && is_array($m['devices'])): ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered mb-2">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Perangkat</th>
                                                            <th>Status</th>
                                                            <th title="Jumlah entri disimpan">Insert</th>
                                                            <th title="Rekap harian guru">Guru</th>
                                                            <th title="Rekap harian siswa">Siswa</th>
                                                            <th title="Terproses jadwal">Jadwal</th>
                                                            <th>Catatan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($m['devices'] as $d): ?>
                                                            <tr>
                                                                <td><?= sanitize($d['device'] ?? '-') ?></td>
                                                                <td>
                                                                    <?php $db = ($d['status'] ?? 'secondary'); $badge = $db === 'success' ? 'success' : ($db === 'warning' ? 'warning' : ($db === 'error' ? 'danger' : 'secondary')); ?>
                                                                    <span class="badge badge-<?= $badge ?>"><?= sanitize(ucfirst($d['status'] ?? '-')) ?></span>
                                                                </td>
                                                                <td><?= (int)($d['inserted'] ?? 0) ?></td>
                                                                <td><?= (int)($d['daily_processed'] ?? 0) ?></td>
                                                                <td><?= (int)($d['student_daily_processed'] ?? 0) ?></td>
                                                                <td><?= (int)($d['schedule_processed'] ?? 0) ?></td>
                                                                <td class="text-truncate" style="max-width:220px;" title="<?= sanitize($d['message'] ?? '') ?>">
                                                                    <?= sanitize($d['message'] ?? '') ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>

                                        <details class="small">
                                            <summary>Lihat detail mentah</summary>
                                            <pre class="small bg-light p-2 rounded border mb-0"><?= sanitize(json_encode($m, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                        </details>
                                    <?php else: ?>
                                        <pre class="small bg-light p-2 rounded border"><?= sanitize(json_encode($log['meta'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <form method="POST" action="<?= route('automation_trigger') ?>" onsubmit="return confirm('Jalankan <?= sanitize($config['title']) ?> sekarang?');">
                                    <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                                    <input type="hidden" name="action" value="<?= sanitize($config['action']) ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-play"></i> Jalankan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
