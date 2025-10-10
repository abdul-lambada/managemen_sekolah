<?php

declare(strict_types=1);

require_once __DIR__ . '/init.php';

$publicRoutes = ['login', 'do_login', 'pengaduan_form', 'pengaduan_submit'];
$page = $_GET['page'] ?? 'dashboard';

if (!in_array($page, $publicRoutes, true) && empty($_SESSION['user'])) {
    redirect(route('login'));
}

if (in_array($page, $publicRoutes, true) && !empty($_SESSION['user']) && $page === 'login') {
    redirect(route('dashboard'));
}

$user = current_user();
$menu = filter_menu_by_role($menu, $user['role'] ?? null);
