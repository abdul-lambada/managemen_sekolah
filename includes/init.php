<?php

declare(strict_types=1);

session_start();

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
            ['label' => 'Kelas', 'page' => 'kelas', 'icon' => 'fas fa-door-open', 'roles' => ['admin']],
            ['label' => 'Jurusan', 'page' => 'jurusan', 'icon' => 'fas fa-layer-group', 'roles' => ['admin']],
        ],
    ],
    [
        'heading' => 'Absensi',
        'items' => [
            ['label' => 'Absensi Guru', 'page' => 'absensi_guru', 'icon' => 'fas fa-chalkboard-teacher', 'roles' => ['admin', 'guru']],
            ['label' => 'Absensi Siswa', 'page' => 'absensi_siswa', 'icon' => 'fas fa-users', 'roles' => ['admin', 'guru']],
            [
                'label' => 'Laporan Absensi',
                'page' => 'laporan_absensi',
                'icon' => 'fas fa-file-alt',
                'roles' => ['admin', 'guru'],
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
