<?php

declare(strict_types=1);

final class PengaduanController extends Controller
{
    public function index(): array
    {
        $this->requireRole('admin');

        $stmt = db()->query('SELECT * FROM pengaduan ORDER BY tanggal_pengaduan DESC');
        $pengaduan = $stmt->fetchAll();

        $response = $this->view('pengaduan/index', [
            'pengaduan' => $pengaduan,
        ], 'Pengaduan');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Pengaduan'
        ];

        return $response;
    }
}
