<?php

declare(strict_types=1);

final class PengaduanController extends Controller
{
    public function index(): array
    {
        $this->requireRole('admin');

        $action = $_GET['action'] ?? 'list';

        return match ($action) {
            'show' => $this->show(),
            'update_status' => $this->updateStatus(),
            default => $this->listing(),
        };
    }

    private function listing(): array
    {
        $model = new Pengaduan();
        $csrfToken = ensure_csrf_token();

        $response = $this->view('pengaduan/index', [
            'pengaduan' => $model->allOrdered(),
            'alert' => flash('pengaduan_alert'),
            'csrfToken' => $csrfToken,
        ], 'Pengaduan');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Pengaduan'
        ];

        return $response;
    }

    private function show(): array
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            flash('pengaduan_alert', 'Pengaduan tidak ditemukan.', 'danger');
            redirect(route('pengaduan'));
        }

        $model = new Pengaduan();
        $pengaduan = $model->find($id, 'id_pengaduan');

        if (!$pengaduan) {
            flash('pengaduan_alert', 'Pengaduan tidak ditemukan.', 'danger');
            redirect(route('pengaduan'));
        }

        $csrfToken = ensure_csrf_token();

        $response = $this->view('pengaduan/show', [
            'pengaduan' => $pengaduan,
            'csrfToken' => $csrfToken,
        ], 'Detail Pengaduan');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Pengaduan' => route('pengaduan'),
            'Detail Pengaduan'
        ];

        return $response;
    }

    private function updateStatus(): string
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect(route('pengaduan'));
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('pengaduan_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('pengaduan'));
        }

        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $redirectUrl = $_POST['redirect'] ?? route('pengaduan');

        $allowedStatus = ['pending', 'diproses', 'selesai'];
        if ($id <= 0 || !in_array($status, $allowedStatus, true)) {
            flash('pengaduan_alert', 'Permintaan tidak valid.', 'danger');
            redirect(route('pengaduan'));
        }

        $model = new Pengaduan();
        $pengaduan = $model->find($id, 'id_pengaduan');
        if (!$pengaduan) {
            flash('pengaduan_alert', 'Pengaduan tidak ditemukan.', 'danger');
            redirect(route('pengaduan'));
        }

        try {
            $model->update($id, ['status' => $status], 'id_pengaduan');
            flash('pengaduan_alert', 'Status pengaduan berhasil diperbarui.', 'success');
        } catch (PDOException $e) {
            flash('pengaduan_alert', 'Gagal memperbarui status: ' . $e->getMessage(), 'danger');
        }

        redirect($redirectUrl ?: route('pengaduan'));
    }
}
