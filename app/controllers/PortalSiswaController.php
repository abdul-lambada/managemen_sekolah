<?php

declare(strict_types=1);

final class PortalSiswaController extends Controller
{
    public function index(): array
    {
        $this->requireRole('siswa');

        $user = current_user();
        $siswaModel = new Siswa();
        $profil = $siswaModel->findByUserId((int) $user['id']);

        if (!$profil) {
            return $this->view('portal_siswa/not_found', [
                'message' => 'Data siswa tidak ditemukan. Silakan hubungi admin.',
            ], 'Portal Siswa');
        }

        $absensiModel = new AbsensiSiswa();
        $recent = $absensiModel->recentForStudent((int) $profil['id_siswa']);
        $summary = $absensiModel->summaryForStudent((int) $profil['id_siswa']);

        $response = $this->view('portal_siswa/index', [
            'profil' => $profil,
            'recent' => $recent,
            'summary' => $summary,
        ], 'Portal Siswa');

        $response['breadcrumbs'] = [
            'Portal Siswa'
        ];

        return $response;
    }

    public function absensi(): array
    {
        $this->requireRole('siswa');

        $user = current_user();
        $siswaModel = new Siswa();
        $profil = $siswaModel->findByUserId((int) $user['id']);

        if (!$profil) {
            redirect(route('portal_siswa'));
        }

        $start = $_GET['start'] ?? null;
        $end = $_GET['end'] ?? null;

        $absensiModel = new AbsensiSiswa();
        $records = $absensiModel->byStudent((int) $profil['id_siswa'], $start ?: null, $end ?: null);
        $summary = $absensiModel->summaryForStudent((int) $profil['id_siswa'], $start ?: null, $end ?: null);

        $response = $this->view('portal_siswa/absensi', [
            'profil' => $profil,
            'records' => $records,
            'summary' => $summary,
            'start' => $start,
            'end' => $end,
        ], 'Absensi Saya');

        $response['breadcrumbs'] = [
            'Portal Siswa' => route('portal_siswa'),
            'Absensi'
        ];

        return $response;
    }

    public function jadwal(): array
    {
        $this->requireRole('siswa');

        $user = current_user();
        $siswaModel = new Siswa();
        $profil = $siswaModel->findByUserId((int) $user['id']);

        if (!$profil) {
            flash('portal_alert', 'Data siswa tidak ditemukan. Silakan hubungi admin.', 'danger');
            redirect(route('portal_siswa'));
        }

        $jadwalModel = new Jadwal();
        $jadwal = $jadwalModel->filter(['kelas' => $profil['id_kelas']]);

        $response = $this->view('portal_siswa/jadwal', [
            'profil' => $profil,
            'jadwal' => $jadwal,
        ], 'Jadwal Pelajaran');

        $response['breadcrumbs'] = [
            'Portal Siswa' => route('portal_siswa'),
            'Jadwal Pelajaran'
        ];

        return $response;
    }
}
