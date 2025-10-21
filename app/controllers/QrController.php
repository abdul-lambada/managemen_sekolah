<?php

declare(strict_types=1);

final class QrController extends Controller
{
    public function pengaduan(): array
    {
        $this->layout = 'auth';
        $defaultUrl = 'https://manajemen-salassika.akarsekawan.my.id/public/index.php?page=pengaduan_form';

        $url = trim($_GET['url'] ?? $defaultUrl);
        $logoUrl = trim($_GET['logo'] ?? '');

        $app = app_settings();
        if ($logoUrl === '' && !empty($app['favicon'])) {
            $logoUrl = uploads_url($app['favicon']);
        }

        return [
            'view' => 'qr/pengaduan',
            'data' => [
                'targetUrl' => $url,
                'logoUrl' => $logoUrl,
            ],
            'title' => 'QR Pengaduan',
            'layout' => 'auth',
        ];
    }
}
