<?php

declare(strict_types=1);

$secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Strict',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['__session_initialized'])) {
    session_regenerate_id(true);
    $_SESSION['__session_initialized'] = true;
}

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../autoload.php';

foreach (glob(__DIR__ . '/../helpers/*.php') as $helper) {
    require_once $helper;
}

$menu = [
    [
        'heading' => 'Manajemen Data',
        'items' => [
            ['label' => 'Guru', 'page' => 'guru', 'icon' => 'fas fa-user-tie', 'roles' => ['admin']],
            ['label' => 'Siswa', 'page' => 'siswa', 'icon' => 'fas fa-user-graduate', 'roles' => ['admin']],
            ['label' => 'Kelas', 'page' => 'kelas', 'icon' => 'fas fa-school', 'roles' => ['admin']],
            ['label' => 'Jurusan', 'page' => 'jurusan', 'icon' => 'fas fa-layer-group', 'roles' => ['admin']],
            ['label' => 'Import Data', 'page' => 'import', 'icon' => 'fas fa-file-upload', 'roles' => ['admin']],
        ],
    ],
    [
        'heading' => 'Absensi',
        'items' => [
            ['label' => 'Jadwal Pelajaran', 'page' => 'jadwal', 'icon' => 'fas fa-calendar-alt', 'roles' => ['admin', 'guru']],
            ['label' => 'Absensi Guru', 'page' => 'absensi_guru', 'icon' => 'fas fa-chalkboard-teacher', 'roles' => ['admin', 'guru']],
            ['label' => 'Absensi Siswa', 'page' => 'absensi_siswa', 'icon' => 'fas fa-users', 'roles' => ['admin', 'guru']],
            ['label' => 'Rekap Harian Siswa', 'page' => 'absensi_siswa_harian', 'icon' => 'fas fa-user-check', 'roles' => ['admin']],
            [
                'label' => 'Laporan Absensi',
                'page' => 'laporan_absensi',
                'icon' => 'fas fa-file-alt',
                'roles' => ['admin', 'guru'],
                'children' => [
                    ['label' => 'Ringkasan Kehadiran', 'page' => 'laporan_absensi'],
                    ['label' => 'Keterlambatan Guru', 'page' => 'laporan_keterlambatan'],
                    ['label' => 'Rekap Siswa per Kelas', 'page' => 'laporan_kelas'],
                ],
            ],
        ],
    ],
    [
        'heading' => 'Integrasi',
        'items' => [
            [
                'label' => 'Fingerprint',
                'page' => 'fingerprint_devices',
                'icon' => 'fas fa-fingerprint',
                'roles' => ['admin'],
                'children' => [
                    ['label' => 'Perangkat', 'page' => 'fingerprint_devices'],
                    ['label' => 'Log', 'page' => 'fingerprint_logs'],
                ],
            ],
            ['label' => 'WhatsApp Config', 'page' => 'whatsapp_config', 'icon' => 'fab fa-whatsapp', 'roles' => ['admin']],
            ['label' => 'Pesan WhatsApp', 'page' => 'whatsapp_logs', 'icon' => 'fas fa-paper-plane', 'roles' => ['admin']],
            ['label' => 'Automasi', 'page' => 'automation', 'icon' => 'fas fa-robot', 'roles' => ['admin']],
        ],
    ],
    [
        'heading' => 'Sistem',
        'items' => [
            ['label' => 'Pengaduan', 'page' => 'pengaduan', 'icon' => 'fas fa-headset', 'roles' => ['admin']],
            ['label' => 'System Stats', 'page' => 'system_stats', 'icon' => 'fas fa-tachometer-alt', 'roles' => ['admin']],
            ['label' => 'Health Check', 'page' => 'health', 'icon' => 'fas fa-heartbeat', 'roles' => ['admin']],
            ['label' => 'Log Aktivitas', 'page' => 'activity_logs', 'icon' => 'fas fa-clipboard-list', 'roles' => ['admin']],
        ],
    ],
];

$portalMenu = [
    [
        'heading' => 'Portal Siswa',
        'items' => [
            ['label' => 'Beranda', 'page' => 'portal_siswa', 'icon' => 'fas fa-home', 'roles' => ['siswa']],
            ['label' => 'Absensi Saya', 'page' => 'portal_siswa_absensi', 'icon' => 'fas fa-calendar-check', 'roles' => ['siswa']],
            ['label' => 'Jadwal Pelajaran', 'page' => 'portal_siswa_jadwal', 'icon' => 'fas fa-book-open', 'roles' => ['siswa']],
        ],
    ],
];

function filter_menu_by_role(array $menu, ?string $role): array
{
    $filtered = [];

    foreach ($menu as $section) {
        $items = [];
        foreach ($section['items'] as $item) {
            $allowedRoles = $item['roles'] ?? ['admin'];
            if ($role && in_array($role, $allowedRoles, true)) {
                $items[] = $item;
            }
        }

        if (!empty($items)) {
            $section['items'] = $items;
            $filtered[] = $section;
        }
    }

    return $filtered;
}

$user = current_user();

if (($user['role'] ?? null) === 'siswa') {
    $menu = filter_menu_by_role($portalMenu, 'siswa');
} else {
    $menu = filter_menu_by_role($menu, $user['role'] ?? null);
}
