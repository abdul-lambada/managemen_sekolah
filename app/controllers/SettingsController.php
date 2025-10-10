<?php

declare(strict_types=1);

final class SettingsController extends Controller
{
    public function index(): array
    {
        $this->requireRole('admin');

        $response = $this->view('settings/index', [
            'sections' => $this->settingsSections(),
        ], 'Pengaturan');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Pengaturan'
        ];

        return $response;
    }

    private function settingsSections(): array
    {
        return [
            [
                'title' => 'Profil',
                'description' => 'Kelola informasi profil dan keamanan akun.',
                'url' => route('profile'),
                'icon' => 'fas fa-user-cog',
            ],
            [
                'title' => 'WhatsApp',
                'description' => 'Perbarui konfigurasi integrasi WhatsApp.',
                'url' => route('whatsapp_config'),
                'icon' => 'fab fa-whatsapp',
            ],
            [
                'title' => 'Automasi',
                'description' => 'Atur automasi dan jadwal sinkronisasi.',
                'url' => route('automation'),
                'icon' => 'fas fa-robot',
            ],
        ];
    }
}
