<?php if (!function_exists('format_bytes_simple')): ?>
<?php
function format_bytes_simple(?int $bytes): string
{
    if ($bytes === null || $bytes < 0) {
        return '-';
    }

    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $index = 0;

    while ($bytes >= 1024 && $index < count($units) - 1) {
        $bytes /= 1024;
        $index++;
    }

    return sprintf('%.2f %s', $bytes, $units[$index]);
}
?>
<?php endif; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-xl-8 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Status Cron</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($checks['cron'] as $key => $item): ?>
                            <?php
                            $status = $item['status'] ?? 'unknown';
                            $badgeClass = match ($status) {
                                'success' => 'success',
                                'warning' => 'warning',
                                'error' => 'danger',
                                default => 'secondary',
                            };
                            ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-capitalize"><?= sanitize(str_replace('_', ' ', $key)) ?></h6>
                                        <p class="mb-1 text-muted small"><?= sanitize($item['label'] ?? 'Tidak diketahui') ?></p>
                                        <?php if (!empty($item['meta'])): ?>
                                            <pre class="small bg-light p-2 rounded mb-0"><?= sanitize(json_encode($item['meta'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge badge-<?= $badgeClass ?> badge-pill text-uppercase"><?= sanitize($status) ?></span>
                                </div>
                                <?php if (!empty($item['updated_at'])): ?>
                                    <small class="text-muted">Terakhir diperbarui: <?= sanitize(indo_datetime($item['updated_at'])) ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Antrian WhatsApp</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">Pending: <span class="badge badge-primary"><?= (int) ($checks['queue']['pending'] ?? 0) ?></span></p>
                    <p class="mb-0">Gagal: <span class="badge badge-danger"><?= (int) ($checks['queue']['failed'] ?? 0) ?></span></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Kapasitas Penyimpanan</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">Folder Upload: <code><?= sanitize($checks['storage']['path'] ?? '-') ?></code></p>
                    <p class="mb-2">Ukuran Folder: <strong><?= sanitize(format_bytes_simple($checks['storage']['folder_size'] ?? null)) ?></strong></p>
                    <p class="mb-2">Disk Total: <strong><?= sanitize(format_bytes_simple($checks['storage']['disk_total'] ?? null)) ?></strong></p>
                    <p class="mb-0">Disk Usage: <strong><?= sanitize($checks['storage']['disk_usage_percent'] !== null ? number_format((float) $checks['storage']['disk_usage_percent'], 2) . '%' : '-') ?></strong></p>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Log Kesalahan PHP</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">Lokasi Log: <code><?= sanitize($checks['errors']['path'] ?? '-') ?></code></p>
                    <p class="mb-3">Status: <span class="badge badge-<?= !empty($checks['errors']['exists']) ? 'success' : 'secondary' ?>"><?= !empty($checks['errors']['exists']) ? 'Tersedia' : 'Tidak ditemukan' ?></span></p>
                    <?php if (!empty($checks['errors']['recent'])): ?>
                        <div class="pre-scrollable" style="max-height: 240px;">
                            <pre class="small bg-dark text-light p-3 rounded mb-0"><?php foreach ($checks['errors']['recent'] as $line): ?><?= sanitize($line) ?>
<?php endforeach; ?></pre>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">Tidak ada catatan terbaru.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
