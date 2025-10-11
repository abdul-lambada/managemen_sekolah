<?php

declare(strict_types=1);

require_once __DIR__ . '/init.php';

$publicRoutes = [
    'login',
    'do_login',
    'forgot_password',
    'do_forgot_password',
    'reset_password',
    'do_reset_password',
    'pengaduan_form',
    'pengaduan_submit'
];
$page = $_GET['page'] ?? 'dashboard';

if (!in_array($page, $publicRoutes, true) && empty($_SESSION['user'])) {
    redirect(route('login'));
}

if (in_array($page, $publicRoutes, true) && !empty($_SESSION['user']) && $page === 'login') {
    $user = $_SESSION['user'];
    $target = $user['role'] === 'siswa' ? route('portal_siswa') : route('dashboard');
    redirect($target);
}

$user = current_user();
$menu = filter_menu_by_role($menu, $user['role'] ?? null);
