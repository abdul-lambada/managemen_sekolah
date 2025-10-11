<?php
$appSettings = app_settings();
$appName = $appSettings['app_name'] ?: APP_NAME;
$appTagline = $appSettings['app_tagline'] ?: $appName;
$faviconUrl = !empty($appSettings['favicon']) ? uploads_url($appSettings['favicon']) : asset('img/undraw_profile.svg');
$pageTitle = $title ?? 'Masuk';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?= sanitize($appTagline) ?>">
    <meta name="author" content="<?= sanitize($appName) ?>">

    <title><?= sanitize($pageTitle) ?> | <?= sanitize($appName) ?></title>
    <link rel="icon" href="<?= sanitize($faviconUrl) ?>">

    <link href="<?= asset('vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700" rel="stylesheet">
    <link href="<?= asset('css/sb-admin-2.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/custom-overrides.css') ?>" rel="stylesheet">
</head>

<body class="bg-gradient-primary">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-8">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4"><?= $appSettings['app_name'] ?: APP_NAME ?></h1>
                                <title><?= $pageTitle ?? ($appSettings['app_name'] ?: APP_NAME) ?> | <?= ($appSettings['app_name'] ?: APP_NAME) ?></title>
                                <link rel="icon" href="<?= !empty($appSettings['favicon']) ? uploads_url($appSettings['favicon']) : asset('img/undraw_profile.svg') ?>">
                            </div>
                            <?php if (!empty($flashSuccess)): ?>
                                <div class="alert alert-<?= sanitize($flashSuccess['type']) ?>">
                                    <?= sanitize($flashSuccess['message']) ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($flash)): ?>
                                <div class="alert alert-<?= sanitize($flash['type']) ?>">
                                    <?= sanitize($flash['message']) ?>
                                </div>
                            <?php endif; ?>
                            <form class="user" method="POST" action="<?= route('do_login') ?>">
                                <input type="hidden" name="csrf_token" value="<?= sanitize($csrfToken) ?>">
                                <div class="form-group">
                                    <input type="text" class="form-control form-control-user" name="username" placeholder="Nama pengguna" required>
                                </div>
                                <div class="form-group">
                                    <input type="password" class="form-control form-control-user" name="password" placeholder="Kata sandi" required>
                                </div>
                                <button type="submit" class="btn btn-primary btn-user btn-block">
                                    Masuk
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= asset('vendor/jquery/jquery.min.js') ?>"></script>
    <script src="<?= asset('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= asset('vendor/jquery-easing/jquery.easing.min.js') ?>"></script>
    <script src="<?= asset('js/sb-admin-2.min.js') ?>"></script>
</body>

</html>
