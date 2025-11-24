<?php

class AbsensiGuruController extends Controller
{
    public function index()
    {
        $this->requireRole('admin', 'guru');

        $start = isset($_GET['start']) ? $_GET['start'] : null;
        $end = isset($_GET['end']) ? $_GET['end'] : null;

        $model = new AbsensiGuru();
        $records = $model->allWithGuru(($start ? $start : null), ($end ? $end : null));

        $export = isset($_GET['export']) ? $_GET['export'] : null;

        if ($export === 'csv') {
            $this->exportCsv($records, $start, $end);
        }

        if ($export === 'pdf') {
            $headers = ['Tanggal', 'Nama Guru', 'NIP', 'Status', 'Jam Masuk', 'Jam Keluar', 'Catatan'];
            $rows = array_map(function ($row) {
                return [
                    $row['tanggal'],
                    $row['nama_guru'],
                    $row['nip'],
                    $row['status_kehadiran'],
                    isset($row['jam_masuk']) ? $row['jam_masuk'] : '-',
                    isset($row['jam_keluar']) ? $row['jam_keluar'] : '-',
                    isset($row['catatan']) ? $row['catatan'] : '-',
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
            $rows = array_map(function ($row) {
                return [
                    $row['tanggal'],
                    $row['nama_guru'],
                    $row['nip'],
                    $row['status_kehadiran'],
                    isset($row['jam_masuk']) ? $row['jam_masuk'] : '-',
                    isset($row['jam_keluar']) ? $row['jam_keluar'] : '-',
                    isset($row['catatan']) ? $row['catatan'] : '-',
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

    private function exportCsv($records, $start, $end)
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
