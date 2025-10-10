<?php

declare(strict_types=1);

$formData = $formData ?? [];
$errors = $errors ?? [];
$alert = $alert ?? null;
?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Profil</h1>
        <a href="<?= route('profile') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
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

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= sanitize($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <img class="img-profile rounded-circle mb-3" src="<?= sanitize(uploads_url($user['avatar'] ?? '')) ?>" alt="Avatar" width="120" height="120">
                    <p class="text-muted mb-0">Avatar saat ini</p>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form method="POST" action="<?= route('profile_update') ?>" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">

                        <div class="form-group">
                            <label for="name">Nama</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= sanitize($formData['name'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Nomor Telepon</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?= sanitize($formData['phone'] ?? '') ?>" placeholder="Opsional">
                        </div>

                        <div class="form-group">
                            <label for="password">Kata Sandi Baru</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Biarkan kosong jika tidak ingin mengubah">
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Konfirmasi Kata Sandi</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Ulangi kata sandi baru">
                        </div>

                        <div class="form-group">
                            <label for="avatar">Avatar (JPG, PNG, WEBP maks 2MB)</label>
                            <input type="file" class="form-control-file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp">
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
