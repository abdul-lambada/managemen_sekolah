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

        if (in_array($export, ['csv', 'pdf', 'excel'], true)) {
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

            $this->exportGeneric($export, 'laporan_absensi', 'Laporan Absensi', $headers, $rows);
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

    private function exportGeneric(string $export, string $filename, string $title, array $headers, array $rows): void
    {
        if ($export === 'pdf') {
            export_array_to_pdf($filename, $title, $headers, $rows, 'landscape');
        }

        if ($export === 'excel') {
            export_array_to_excel($filename, $headers, $rows);
        }

        if ($export === 'csv') {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Ymd_His') . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');

            $output = fopen('php://output', 'w');
            fputcsv($output, $headers);
            foreach ($rows as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
            exit;
        }
    }
}
