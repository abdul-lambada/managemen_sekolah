<?php

declare(strict_types=1);

$sections = $sections ?? [];
?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Pengaturan</h1>
        <p class="text-muted mb-0">Kelola konfigurasi sistem dan preferensi Anda.</p>
    </div>

    <div class="row">
        <?php foreach ($sections as $section): ?>
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    <?= sanitize($section['title']) ?>
                                </div>
                                <div class="mb-3 text-gray-600 small">
                                    <?= sanitize($section['description']) ?>
                                </div>
                                <a href="<?= sanitize($section['url']) ?>" class="btn btn-sm btn-primary">
                                    Buka
                                </a>
                            </div>
                            <div class="col-auto">
                                <i class="<?= sanitize($section['icon']) ?> fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
