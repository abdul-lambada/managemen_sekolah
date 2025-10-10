<?php

declare(strict_types=1);

class AbsensiGuruController extends Controller
{
    public function index(): array|string
    {
        $this->requireRole('admin', 'guru');

        $start = $_GET['start'] ?? null;
        $end = $_GET['end'] ?? null;

        $model = new AbsensiGuru();
        $records = $model->allWithGuru($start ?: null, $end ?: null);

        $export = $_GET['export'] ?? null;

        if ($export === 'csv') {
            $this->exportCsv($records, $start, $end);
        }

        if ($export === 'pdf') {
            $headers = ['Tanggal', 'Nama Guru', 'NIP', 'Status', 'Jam Masuk', 'Jam Keluar', 'Catatan'];
            $rows = array_map(static function (array $row): array {
                return [
                    $row['tanggal'],
                    $row['nama_guru'],
                    $row['nip'],
                    $row['status_kehadiran'],
                    $row['jam_masuk'] ?? '-',
                    $row['jam_keluar'] ?? '-',
                    $row['catatan'] ?? '-',
                ];
            }, $records);

            export_array_to_pdf(
                'absensi_guru',
                'Laporan Absensi Guru',
                $headers,
                $rows,
                'landscape'
            );
        }

        if ($export === 'excel') {
            $headers = ['Tanggal', 'Nama Guru', 'NIP', 'Status', 'Jam Masuk', 'Jam Keluar', 'Catatan'];
            $rows = array_map(static function (array $row): array {
                return [
                    $row['tanggal'],
                    $row['nama_guru'],
                    $row['nip'],
                    $row['status_kehadiran'],
                    $row['jam_masuk'] ?? '-',
                    $row['jam_keluar'] ?? '-',
                    $row['catatan'] ?? '-',
                ];
            }, $records);

            export_array_to_excel('absensi_guru', $headers, $rows);
        }

        $response = $this->view('absensi_guru/index', [
            'records' => $records,
            'start' => $start,
            'end' => $end,
        ], 'Absensi Guru');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Absensi Guru'
        ];
        return $response;
    }

    private function exportCsv(array $records, ?string $start, ?string $end): void
    {
        $filename = 'absensi_guru_' . ($start ?: 'all') . '_' . ($end ?: date('Ymd')) . '_' . date('His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Tanggal', 'Nama Guru', 'NIP', 'Status', 'Jam Masuk', 'Jam Keluar', 'Catatan']);

        foreach ($records as $row) {
            fputcsv($output, [
                $row['tanggal'],
                $row['nama_guru'],
                $row['nip'],
                $row['status_kehadiran'],
                $row['jam_masuk'],
                $row['jam_keluar'],
                $row['catatan'],
            ]);
        }

        fclose($output);
        exit;
    }
}
