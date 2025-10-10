<?php

declare(strict_types=1);

class AbsensiSiswaController extends Controller
{
    public function index(): array|string
    {
        $this->requireRole('admin', 'guru');

        $start = $_GET['start'] ?? null;
        $end = $_GET['end'] ?? null;
        $kelasId = isset($_GET['kelas']) ? (int) $_GET['kelas'] : null;

        $model = new AbsensiSiswa();
        $records = $model->allWithSiswa($start ?: null, $end ?: null, $kelasId ?: null);

        $export = $_GET['export'] ?? null;

        if ($export === 'csv') {
            $this->exportCsv($records, $start, $end, $kelasId);
        }

        if ($export === 'pdf') {
            $headers = ['Tanggal', 'Nama Siswa', 'NISN', 'NIS', 'Kelas', 'Jurusan', 'Status', 'Jam Masuk', 'Jam Keluar', 'Catatan'];
            $rows = array_map(static function (array $row): array {
                return [
                    $row['tanggal'],
                    $row['nama_siswa'],
                    $row['nisn'],
                    $row['nis'],
                    $row['nama_kelas'],
                    $row['nama_jurusan'],
                    $row['status_kehadiran'],
                    $row['jam_masuk'] ?? '-',
                    $row['jam_keluar'] ?? '-',
                    $row['catatan'] ?? '-',
                ];
            }, $records);

            export_array_to_pdf(
                'absensi_siswa',
                'Laporan Absensi Siswa',
                $headers,
                $rows,
                'landscape'
            );
        }

        $response = $this->view('absensi_siswa/index', [
            'records' => $records,
            'start' => $start,
            'end' => $end,
            'kelasOptions' => (new Kelas())->options(),
            'kelasId' => $kelasId,
        ], 'Absensi Siswa');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Absensi Siswa'
        ];

        return $response;
    }

    private function exportCsv(array $records, ?string $start, ?string $end, ?int $kelasId): void
    {
        $kelasSegment = $kelasId ? 'kelas-' . $kelasId : 'semua';
        $filename = 'absensi_siswa_' . $kelasSegment . '_' . ($start ?: 'all') . '_' . ($end ?: date('Ymd')) . '_' . date('His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Tanggal', 'Nama Siswa', 'NISN', 'NIS', 'Kelas', 'Jurusan', 'Status', 'Jam Masuk', 'Jam Keluar', 'Catatan']);

        foreach ($records as $row) {
            fputcsv($output, [
                $row['tanggal'],
                $row['nama_siswa'],
                $row['nisn'],
                $row['nis'],
                $row['nama_kelas'],
                $row['nama_jurusan'],
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
