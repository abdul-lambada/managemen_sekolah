<?php
$appSettings = app_settings();
$appName = $appSettings['app_name'] ?: APP_NAME;
$appTagline = $appSettings['app_tagline'] ?: '';
$pageTitle = $title ?? $appName;
$metaDescription = $appTagline !== '' ? $appTagline : $appName;
$faviconUrl = !empty($appSettings['favicon']) ? uploads_url($appSettings['favicon']) : asset('img/undraw_profile.svg');
?>
<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="<?= sanitize($metaDescription) ?>">
	<meta name="author" content="<?= sanitize($appName) ?>">

	<title><?= sanitize($pageTitle) ?> | <?= sanitize($appName) ?></title>
	<link rel="icon" href="<?= sanitize($faviconUrl) ?>">

	<link href="<?= asset('vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
	<link href="<?= asset('css/sb-admin-2.min.css') ?>" rel="stylesheet">
	<link href="<?= asset('css/custom-overrides.css') ?>" rel="stylesheet">
	<link href="<?= asset('vendor/datatables/dataTables.bootstrap4.min.css') ?>" rel="stylesheet">
	<?php if (!empty($styles ?? [])): ?>
		<?php foreach ($styles as $style): ?>
			<link rel="stylesheet" href="<?= asset($style) ?>">
		<?php endforeach; ?>
	<?php endif; ?>
</head>

<body id="page-top">

<div id="wrapper">
