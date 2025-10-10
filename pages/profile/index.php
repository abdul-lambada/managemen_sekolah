<?php

declare(strict_types=1);

$user = $user ?? current_user();

?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Profil Saya</h1>
        <a href="<?= route('profile_edit') ?>" class="btn btn-primary">
            <i class="fas fa-user-edit"></i> Edit Profil
        </a>
    </div>

    <?php if (!empty($alert)): ?>
        <div class="alert alert-<?= sanitize($alert['type']) ?> alert-dismissible fade show" role="alert">
            <?= sanitize($alert['message']) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <img class="img-profile rounded-circle mb-3" src="<?= sanitize(uploads_url($user['avatar'] ?? '')) ?>" alt="Avatar" width="120" height="120">
                    <h5 class="font-weight-bold mb-0"><?= sanitize($user['name'] ?? '-') ?></h5>
                    <span class="text-muted">Peran: <?= sanitize(ucfirst($user['role'] ?? '')) ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Akun</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nama</dt>
                        <dd class="col-sm-8"><?= sanitize($user['name'] ?? '-') ?></dd>

                        <dt class="col-sm-4">Peran</dt>
                        <dd class="col-sm-8"><?= sanitize(ucfirst($user['role'] ?? '-')) ?></dd>

                        <dt class="col-sm-4">Tanggal Login Terakhir</dt>
                        <dd class="col-sm-8"><?= sanitize($_SESSION['last_login_at'] ?? '-') ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
