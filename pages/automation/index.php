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
                                    <pre class="small bg-light p-2 rounded border"><?= sanitize(json_encode($log['meta'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
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
