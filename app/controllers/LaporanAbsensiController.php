<?php

declare(strict_types=1);

class LaporanAbsensiController extends Controller
{
    public function index(): array|string
    {
        $this->requireRole('admin', 'guru');

        $periode = $_GET['periode'] ?? 'Bulanan';
        $start = $_GET['start'] ?? null;
        $end = $_GET['end'] ?? null;

        $model = new LaporanAbsensi();
        $reports = $model->summary($start ?: null, $end ?: null, $periode ?: 'Bulanan');

        $export = $_GET['export'] ?? null;

        if ($export === 'csv') {
            $this->exportCsv($reports, $periode, $start, $end);
        }

        if ($export === 'pdf') {
            $headers = ['Periode', 'Tanggal Mulai', 'Tanggal Akhir', 'Jumlah Hadir', 'Jumlah Tidak Hadir'];
            $rows = array_map(static function (array $report): array {
                return [
                    $report['periode'],
                    $report['tanggal_mulai'],
                    $report['tanggal_akhir'],
                    $report['jumlah_hadir'],
                    $report['jumlah_tidak_hadir'],
                ];
            }, $reports);

            export_array_to_pdf(
                'laporan_absensi',
                'Laporan Absensi',
                $headers,
                $rows,
                'landscape'
            );
        }

        if ($export === 'excel') {
            $headers = ['Periode', 'Tanggal Mulai', 'Tanggal Akhir', 'Jumlah Hadir', 'Jumlah Tidak Hadir'];
            $rows = array_map(static function (array $report): array {
                return [
                    $report['periode'],
                    $report['tanggal_mulai'],
                    $report['tanggal_akhir'],
                    $report['jumlah_hadir'],
                    $report['jumlah_tidak_hadir'],
                ];
            }, $reports);

            export_array_to_excel('laporan_absensi', $headers, $rows);
        }

        $totalHadir = array_sum(array_column($reports, 'jumlah_hadir'));
        $totalTidakHadir = array_sum(array_column($reports, 'jumlah_tidak_hadir'));

        $response = $this->view('laporan_absensi/index', [
            'reports' => $reports,
            'periode' => $periode,
            'start' => $start,
            'end' => $end,
            'totalHadir' => $totalHadir,
            'totalTidakHadir' => $totalTidakHadir,
        ], 'Laporan Absensi');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Laporan Absensi'
        ];

        $response['scripts'] = ['vendor/chart.js/Chart.min.js'];

        return $response;
    }

    private function exportCsv(array $reports, string $periode, ?string $start, ?string $end): void
    {
        $filename = 'laporan_absensi_' . strtolower($periode) . '_' . ($start ?: 'all') . '_' . ($end ?: date('Ymd')) . '_' . date('His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Periode', 'Tanggal Mulai', 'Tanggal Akhir', 'Jumlah Hadir', 'Jumlah Tidak Hadir']);

        foreach ($reports as $report) {
            fputcsv($output, [
                $report['periode'],
                $report['tanggal_mulai'],
                $report['tanggal_akhir'],
                $report['jumlah_hadir'],
                $report['jumlah_tidak_hadir'],
            ]);
        }

        fclose($output);
        exit;
    }
}
