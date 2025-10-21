<?php

declare(strict_types=1);

final class AbsensiSiswaHarianController extends Controller
{
    public function index(): array|string
    {
        $this->requireRole('admin');

        $start = $_GET['start'] ?? null;
        $end = $_GET['end'] ?? null;
        $kelasId = isset($_GET['kelas']) && $_GET['kelas'] !== '' ? (int) $_GET['kelas'] : null;

        $model = new KehadiranSiswaHarian();
        $records = $model->allWithSiswa($start ?: null, $end ?: null, $kelasId);
        $kelasOptions = $model->kelasOptions();

        $export = $_GET['export'] ?? null;
        if ($export === 'csv') {
            $this->exportCsv($records, $start, $end, $kelasId);
        }

        $response = $this->view('absensi_siswa_harian/index', [
            'records' => $records,
            'start' => $start,
            'end' => $end,
            'kelasId' => $kelasId,
            'kelasOptions' => $kelasOptions,
        ], 'Rekap Harian Siswa');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Absensi' => route('absensi_siswa'),
            'Rekap Harian Siswa'
        ];

        return $response;
    }

    private function exportCsv(array $records, ?string $start, ?string $end, ?int $kelasId): void
    {
        $suffix = ($start ?: 'all') . '_' . ($end ?: date('Ymd'));
        $filename = 'rekap_harian_siswa_' . $suffix . '_' . date('His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Tanggal', 'Nama Siswa', 'NIS', 'NISN', 'Kelas', 'Check-in Pagi', 'Check-out Sore']);

        foreach ($records as $row) {
            fputcsv($output, [
                $row['tanggal'],
                $row['nama_siswa'],
                $row['nis'],
                $row['nisn'],
                ($row['nama_jurusan'] ?? '') . ' - ' . ($row['nama_kelas'] ?? ''),
                $row['check_in_pagi'] ?? '-',
                $row['check_out_sore'] ?? '-',
            ]);
        }

        fclose($output);
        exit;
    }
}
