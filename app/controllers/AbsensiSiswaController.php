<?php

class AbsensiSiswaController extends Controller
{
    public function index()
    {
        $this->requireRole('admin', 'guru');

        $start = isset($_GET['start']) ? $_GET['start'] : null;
        $end = isset($_GET['end']) ? $_GET['end'] : null;
        $kelasId = isset($_GET['kelas']) ? (int) $_GET['kelas'] : null;

        $model = new AbsensiSiswa();
        $records = $model->allWithSiswa(($start ? $start : null), ($end ? $end : null), ($kelasId ? $kelasId : null));

        $export = isset($_GET['export']) ? $_GET['export'] : null;

        if ($export === 'csv') {
            $this->exportCsv($records, $start, $end, $kelasId);
        }

        if ($export === 'pdf') {
            $headers = ['Tanggal', 'Nama Siswa', 'NISN', 'NIS', 'Kelas', 'Jurusan', 'Status', 'Jam Masuk', 'Jam Keluar', 'Catatan'];
            $rows = array_map(function ($row) {
                return [
                    $row['tanggal'],
                    $row['nama_siswa'],
                    $row['nisn'],
                    $row['nis'],
                    $row['nama_kelas'],
                    $row['nama_jurusan'],
                    $row['status_kehadiran'],
                    isset($row['jam_masuk']) ? $row['jam_masuk'] : '-',
                    isset($row['jam_keluar']) ? $row['jam_keluar'] : '-',
                    isset($row['catatan']) ? $row['catatan'] : '-',
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

    private function exportCsv($records, $start, $end, $kelasId)
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
