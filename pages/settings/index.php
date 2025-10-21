<?php

declare(strict_types=1);

$sections = $sections ?? [];
$currentSettings = $currentSettings ?? app_settings();
$alert = $alert ?? null;
$csrfToken = $csrfToken ?? ensure_csrf_token();
$faviconPreview = !empty($currentSettings['favicon'])
    ? uploads_url($currentSettings['favicon'])
    : asset('img/undraw_profile.svg');
?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Pengaturan</h1>
            <p class="text-muted mb-0">Kelola konfigurasi sistem dan preferensi Anda.</p>
        </div>
    </div>

    <?php if (!empty($alert)): ?>
        <div class="alert alert-<?= sanitize($alert['type']) ?> alert-dismissible fade show" role="alert">
            <?= nl2br(sanitize($alert['message'])) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Pengaturan Umum</h6>
                </div>
                <div class="card-body">
                    <form action="<?= route('settings_update') ?>" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                        <input type="hidden" name="favicon_existing" value="<?= sanitize($currentSettings['favicon'] ?? '') ?>">

                        <div class="form-group">
                            <label for="app_name">Nama Aplikasi</label>
                            <input type="text" class="form-control" id="app_name" name="app_name"
                                   value="<?= sanitize($currentSettings['app_name'] ?? '') ?>" required>
                            <small class="form-text text-muted">Nama ini akan tampil di judul halaman dan navigasi.</small>
                        </div>

                        <div class="form-group">
                            <label for="app_tagline">Tagline</label>
                            <input type="text" class="form-control" id="app_tagline" name="app_tagline"
                                   value="<?= sanitize($currentSettings['app_tagline'] ?? '') ?>"
                                   placeholder="Contoh: Sistem Informasi Sekolah Terpadu">
                            <small class="form-text text-muted">Opsional. Digunakan sebagai deskripsi singkat situs.</small>
                        </div>

                        <div class="form-group">
                            <label for="favicon">Favicon</label>
                            <div class="mb-2 d-flex align-items-center">
                                <img src="<?= sanitize($faviconPreview) ?>" alt="Favicon saat ini"
                                     class="img-thumbnail mr-3" style="width: 48px; height: 48px; object-fit: contain;">
                                <div class="text-muted small">PNG, ICO, atau SVG. Biarkan kosong jika tidak ingin mengubah.</div>
                            </div>
                            <input type="file" class="form-control-file" id="favicon" name="favicon" accept=".png,.ico,.svg">
                        </div>

                        <hr>
                        <h6 class="font-weight-bold text-primary mb-3">Pengaturan Waktu Absensi</h6>

                        <?php
                        $ms = $currentSettings['attendance_morning_start'] ?? '';
                        $me = $currentSettings['attendance_morning_end'] ?? '';
                        $es = $currentSettings['attendance_evening_start'] ?? '';
                        $ee = $currentSettings['attendance_evening_end'] ?? '';
                        $toHHMM = static function ($v) { return $v ? substr((string)$v, 0, 5) : ''; };
                        ?>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="attendance_morning_start">Mulai Check-in Pagi</label>
                                <input type="time" class="form-control" id="attendance_morning_start" name="attendance_morning_start" step="60"
                                       value="<?= sanitize($toHHMM($ms)) ?>" placeholder="05:00">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="attendance_morning_end">Akhir Check-in Pagi</label>
                                <input type="time" class="form-control" id="attendance_morning_end" name="attendance_morning_end" step="60"
                                       value="<?= sanitize($toHHMM($me)) ?>" placeholder="09:00">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="attendance_evening_start">Mulai Check-out Sore</label>
                                <input type="time" class="form-control" id="attendance_evening_start" name="attendance_evening_start" step="60"
                                       value="<?= sanitize($toHHMM($es)) ?>" placeholder="14:00">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="attendance_evening_end">Akhir Check-out Sore</label>
                                <input type="time" class="form-control" id="attendance_evening_end" name="attendance_evening_end" step="60"
                                       value="<?= sanitize($toHHMM($ee)) ?>" placeholder="23:59">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Modul Lainnya</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($sections as $section): ?>
                            <div class="col-xl-6 col-md-6 mb-4">
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
                                                <a href="<?= sanitize($section['url']) ?>" class="btn btn-sm btn-outline-primary">
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
            </div>
        </div>
    </div>
</div>
