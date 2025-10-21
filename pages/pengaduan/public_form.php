<?php
$appSettings = app_settings();
$appName = $appSettings['app_name'] ?: APP_NAME;
$appTagline = $appSettings['app_tagline'] ?: $appName;
$faviconUrl = !empty($appSettings['favicon']) ? uploads_url($appSettings['favicon']) : asset('img/undraw_profile.svg');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= sanitize($appTagline) ?>">
    <meta name="author" content="<?= sanitize($appName) ?>">
    <title><?= sanitize($title ?? 'Form Pengaduan') ?> | <?= sanitize($appName) ?></title>
    <link rel="icon" href="<?= sanitize($faviconUrl) ?>">
    <link href="<?= asset('vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700" rel="stylesheet">
    <link href="<?= asset('css/sb-admin-2.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/custom-overrides.css') ?>" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #4e73df, #1cc88a);
            min-height: 100vh;
        }

        .card {
            border: none;
            border-radius: 1rem;
        }

        .card-header {
            border-radius: 1rem 1rem 0 0;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-9 col-md-10">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h2 class="h4 mb-0">Form Pengaduan</h2>
                        <p class="mb-0 small">Sampaikan keluhan, saran, atau masukan Anda kepada sekolah.</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($flash)): ?>
                            <div class="alert alert-<?= sanitize($flash['type']) ?>">
                                <?= sanitize($flash['message']) ?>
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

                        <form method="POST" action="<?= route('pengaduan_submit') ?>" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">

                            <div class="form-group">
                                <label for="nama_pelapor">Nama Pelapor <span class="text-danger">*</span></label>
                                <input type="text" id="nama_pelapor" name="nama_pelapor" class="form-control" value="<?= sanitize($formData['nama_pelapor'] ?? '') ?>" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="role_pelapor">Peran Pelapor <span class="text-danger">*</span></label>
                                    <select id="role_pelapor" name="role_pelapor" class="form-control" required>
                                        <option value="">-- Pilih Peran --</option>
                                        <?php
                                        $roles = ['siswa' => 'Siswa', 'guru' => 'Guru', 'umum' => 'Umum'];
                                        foreach ($roles as $value => $label):
                                            $selected = (($formData['role_pelapor'] ?? '') === $value) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $value ?>" <?= $selected ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="kategori">Kategori <span class="text-danger">*</span></label>
                                    <select id="kategori" name="kategori" class="form-control" required>
                                        <option value="">-- Pilih Kategori --</option>
                                        <?php
                                        $categories = [
                                            'saran' => 'Saran',
                                            'kritik' => 'Kritik',
                                            'pembelajaran' => 'Pembelajaran',
                                            'organisasi' => 'Organisasi',
                                            'administrasi' => 'Administrasi',
                                            'lainnya' => 'Lainnya',
                                        ];
                                        foreach ($categories as $value => $label):
                                            $selected = (($formData['kategori'] ?? '') === $value) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $value ?>" <?= $selected ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="no_wa">Nomor WhatsApp</label>
                                    <input type="text" id="no_wa" name="no_wa" class="form-control" placeholder="Contoh: 6281234567890" value="<?= sanitize($formData['no_wa'] ?? '') ?>">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="email_pelapor">Email</label>
                                    <input type="email" id="email_pelapor" name="email_pelapor" class="form-control" value="<?= sanitize($formData['email_pelapor'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="judul_pengaduan">Judul Pengaduan <span class="text-danger">*</span></label>
                                <input type="text" id="judul_pengaduan" name="judul_pengaduan" class="form-control" value="<?= sanitize($formData['judul_pengaduan'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="isi_pengaduan">Isi Pengaduan <span class="text-danger">*</span></label>
                                <textarea id="isi_pengaduan" name="isi_pengaduan" class="form-control" rows="5" required><?= sanitize($formData['isi_pengaduan'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="keterangan">Keterangan Tambahan</label>
                                <textarea id="keterangan" name="keterangan" class="form-control" rows="3"><?= sanitize($formData['keterangan'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="file_pendukung">File Pendukung (JPG, PNG, PDF maks 2MB)</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="file_pendukung" name="file_pendukung" accept=".jpg,.jpeg,.png,.pdf">
                                    <label class="custom-file-label" for="file_pendukung">Pilih file...</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-paper-plane"></i> Kirim Pengaduan
                            </button>
                        </form>
                    </div>
                    <div class="card-footer text-center small text-muted">
                        &copy; <?= date('Y') ?> <?= sanitize($appName) ?>. Hak cipta dilindungi.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= asset('vendor/jquery/jquery.min.js') ?>"></script>
    <script src="<?= asset('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script>
        $(function () {
            $('.custom-file-input').on('change', function () {
                const fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass('selected').html(fileName);
            });
        });
    </script>
</body>

</html>
