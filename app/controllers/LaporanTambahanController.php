<?php

declare(strict_types=1);

final class LaporanTambahanController extends Controller
{
    private LaporanTambahan $reportModel;

    public function __construct()
    {
        $this->reportModel = new LaporanTambahan();
    }

    public function keterlambatan(): array|string
    {
        $this->requireRole('admin', 'guru');

        $start = $_GET['start'] ?? null;
        $end = $_GET['end'] ?? null;
        $export = $_GET['export'] ?? null;

        $records = $this->reportModel->guruTerlambat($start ?: null, $end ?: null);

        if (in_array($export, ['csv', 'pdf', 'excel'], true)) {
            $headers = ['Tanggal', 'Nama Guru', 'Mapel', 'Kelas', 'Jam Terjadwal', 'Jam Masuk', 'Menit Terlambat'];
            $rows = array_map(static function (array $row): array {
                return [
                    $row['tanggal'],
                    $row['nama_guru'],
                    $row['nama_mapel'],
                    $row['nama_kelas'],
                    substr($row['jam_mulai'], 0, 5) . ' - ' . substr($row['jam_selesai'], 0, 5),
                    $row['jam_masuk'] ?? '-',
                    (string) ($row['menit_terlambat'] ?? 0),
                ];
            }, $records);

            $this->export($export, 'laporan_keterlambatan_guru', 'Laporan Keterlambatan Guru', $headers, $rows);
        }

        $response = $this->view('laporan_tambahan/keterlambatan', [
            'records' => $records,
            'start' => $start,
            'end' => $end,
        ], 'Laporan Keterlambatan Guru');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Laporan Keterlambatan'
        ];

        return $response;
    }

    public function kelas(): array|string
    {
        $this->requireRole('admin', 'guru');

        $kelasId = isset($_GET['kelas']) && $_GET['kelas'] !== '' ? (int) $_GET['kelas'] : null;
        $start = $_GET['start'] ?? null;
        $end = $_GET['end'] ?? null;
        $export = $_GET['export'] ?? null;

        $records = $this->reportModel->rekapSiswaPerKelas($kelasId, $start ?: null, $end ?: null);

        if (in_array($export, ['csv', 'pdf', 'excel'], true)) {
            $headers = ['Jurusan', 'Kelas', 'Hadir', 'Izin', 'Sakit', 'Alpa', 'Total'];
            $rows = array_map(static function (array $row): array {
                return [
                    $row['nama_jurusan'],
                    $row['nama_kelas'],
                    (string) $row['hadir'],
                    (string) $row['izin'],
                    (string) $row['sakit'],
                    (string) $row['alpa'],
                    (string) $row['total'],
                ];
            }, $records);

            $this->export($export, 'laporan_absensi_siswa_kelas', 'Rekap Absensi Siswa per Kelas', $headers, $rows);
        }

        $response = $this->view('laporan_tambahan/kelas', [
            'records' => $records,
            'kelasId' => $kelasId,
            'start' => $start,
            'end' => $end,
            'kelasOptions' => (new Kelas())->options(),
        ], 'Rekap Absensi Siswa per Kelas');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Rekap Absensi Siswa per Kelas'
        ];

        return $response;
    }

    private function export(string $type, string $filename, string $title, array $headers, array $rows): void
    {
        if ($type === 'pdf') {
            export_array_to_pdf($filename, $title, $headers, $rows, 'landscape');
        }

        if ($type === 'excel') {
            export_array_to_excel($filename, $headers, $rows);
        }

        if ($type === 'csv') {
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
