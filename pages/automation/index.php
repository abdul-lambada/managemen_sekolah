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

    <style>
        .automation-card-pre { white-space: pre-wrap; word-break: break-word; overflow: auto; max-height: 240px; }
        .automation-devices { max-height: 240px; overflow: auto; }
        .automation-devices table { table-layout: fixed; width: 100%; }
        .automation-devices th, .automation-devices td { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .automation-details pre { white-space: pre-wrap; word-break: break-word; overflow: auto; max-height: 280px; }
        .automation-note { word-break: break-word; }
        .automation-card { overflow: hidden; }
        details.automation-details > summary { cursor: pointer; }
    </style>

    <div class="row">
        <?php foreach ([
            'whatsapp' => ['title' => 'Dispatch WhatsApp', 'icon' => 'fa-paper-plane', 'action' => 'whatsapp'],
            'fingerprint' => ['title' => 'Sync Fingerprint', 'icon' => 'fa-fingerprint', 'action' => 'fingerprint'],
        ] as $key => $config): ?>
            <?php $log = $logs[$key] ?? ['badge' => 'secondary', 'label' => 'Belum ada data', 'meta' => []]; ?>
            <div class="col-xl-6 col-md-12 mb-4">
                <div class="card border-left-<?= sanitize($log['badge']) ?> shadow h-100 automation-card">
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
                                            <div class="table-responsive automation-devices">
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
                                                                <td class="text-truncate automation-note" style="max-width:220px;" title="<?= sanitize($d['message'] ?? '') ?>">
                                                                    <?= sanitize($d['message'] ?? '') ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>

                                        <details class="small automation-details">
                                            <summary>Lihat detail mentah</summary>
                                            <pre class="small bg-light p-2 rounded border mb-0 automation-card-pre"><?= sanitize(json_encode($m, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                        </details>
                                    <?php else: ?>
                                        <pre class="small bg-light p-2 rounded border automation-card-pre"><?= sanitize(json_encode($log['meta'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <form id="form-<?= sanitize($key) ?>" class="automation-run-form" method="POST" action="<?= route('automation_trigger') ?>">
                                    <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                                    <input type="hidden" name="action" value="<?= sanitize($config['action']) ?>">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#confirmRunModal" data-form="form-<?= sanitize($key) ?>" data-title="<?= sanitize($config['title']) ?>">
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
    <div class="modal fade" id="confirmRunModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmRunModalLabel">Konfirmasi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="confirmRunModalMessage">Apakah Anda yakin?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="confirmRunModalConfirm">OK</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    (function(){
        var targetForm = null;
        $('#confirmRunModal').on('show.bs.modal', function (e) {
            var btn = $(e.relatedTarget);
            var formId = btn.data('form');
            targetForm = document.getElementById(formId);
            var title = btn.data('title');
            $('#confirmRunModalMessage').text('Jalankan ' + title + ' sekarang?');
        });
        $('#confirmRunModalConfirm').on('click', function(){
            if (targetForm) targetForm.submit();
        });
    })();
    </script>
</div>
