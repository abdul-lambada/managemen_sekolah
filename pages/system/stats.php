<?php

declare(strict_types=1);

$stats = $stats ?? [];
?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Statistik Sistem</h1>
        <p class="text-muted mb-0">Ringkasan metrik penting aplikasi.</p>
    </div>

    <div class="row">
        <?php foreach ($stats as $stat): ?>
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    <?= sanitize(str_replace('_', ' ', $stat['key'])) ?>
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php if (is_array($stat['value'])): ?>
                                        <ul class="mb-0 pl-3">
                                            <?php foreach ($stat['value'] as $key => $value): ?>
                                                <li><?= sanitize("{$key}: {$value}") ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <?= sanitize((string) $stat['value']) ?>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted">Diperbarui: <?= sanitize($stat['updated_at'] ?? '-') ?></small>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($stats)): ?>
        <div class="alert alert-info">
            Data statistik belum tersedia.
        </div>
    <?php endif; ?>
</div>
