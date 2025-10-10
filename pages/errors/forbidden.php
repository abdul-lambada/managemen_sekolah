<?php

declare(strict_types=1);

$message = $message ?? 'Anda tidak memiliki izin untuk mengakses halaman ini.';
$backUrl = $backUrl ?? route('dashboard');
?>
<div class="container-fluid">
    <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
        <div class="col-lg-6">
            <div class="card border-left-danger shadow h-100 py-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-lock fa-3x text-danger"></i>
                    </div>
                    <h1 class="display-4 text-danger">403</h1>
                    <h2 class="h4 text-gray-800 mb-3">Akses Ditolak</h2>
                    <p class="mb-4 text-gray-600">
                        <?= sanitize($message) ?>
                    </p>
                    <a href="<?= sanitize($backUrl) ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
