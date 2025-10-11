<?php
$appSettings = app_settings();
$appName = $appSettings['app_name'] ?: APP_NAME;
$pageTitle = $title ?? 'Lupa Kata Sandi';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= sanitize($pageTitle) ?> | <?= sanitize($appName) ?></title>
    <link href="<?= asset('vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet" type="text/css">
    <link href="<?= asset('css/sb-admin-2.min.css') ?>" rel="stylesheet">
</head>

<body class="bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-8">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h1 class="h4 text-gray-900">Lupa Kata Sandi</h1>
                            <p class="text-muted mb-0">Masukkan email atau nama pengguna Anda.</p>
                        </div>
                        <?php if (!empty($flashError)): ?>
                            <div class="alert alert-<?= sanitize($flashError['type']) ?>">
                                <?= sanitize($flashError['message']) ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST" action="<?= route('do_forgot_password') ?>">
                            <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                            <div class="form-group">
                                <label for="identifier">Email / Nama Pengguna</label>
                                <input type="text" class="form-control" id="identifier" name="identifier" required autofocus>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Buat Token Reset</button>
                            <a href="<?= route('login') ?>" class="btn btn-link btn-block">Kembali ke Login</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= asset('vendor/jquery/jquery.min.js') ?>"></script>
    <script src="<?= asset('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
</body>

</html>
