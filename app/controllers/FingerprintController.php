<?php

declare(strict_types=1);

class FingerprintController extends Controller
{
    public function index(): array|string
    {
        $this->requireRole('admin');

        $action = $_GET['action'] ?? 'list';

        return match ($action) {
            'create' => $this->create(),
            'store' => $this->store(),
            'edit' => $this->edit(),
            'update' => $this->update(),
            'delete' => $this->delete(),
            'logs' => $this->buildLogs(),
            'sync' => $this->sync(),
            'uids' => $this->uids(),
            'store_uid' => $this->store_uid(),
            'delete_uid' => $this->delete_uid(),
            'uids_siswa' => $this->uids_siswa(),
            'store_uid_siswa' => $this->store_uid_siswa(),
            'delete_uid_siswa' => $this->delete_uid_siswa(),
            default => $this->listing(),
        };
    }

    private function listing(): array
    {
        $deviceModel = new FingerprintDevice();
        $devices = $deviceModel->all();
        // Use active() to compute active count to avoid driver-specific count filtering
        $activeDevices = method_exists($deviceModel, 'active') ? $deviceModel->active() : [];
        $activeCount = is_array($activeDevices) ? count($activeDevices) : 0;

        // Compute last sync summary from logs (recent 60 minutes)
        try {
            $summary = $this->computeLastSyncSummary(60);
        } catch (Throwable $e) {
            $summary = ['last_sync_at' => null, 'connected_devices' => 0, 'total_logs' => 0];
        }
        try {
            $lastSyncMap = $this->computeLastSyncPerDevice($devices, 1440);
        } catch (Throwable $e) {
            $lastSyncMap = [];
        }

        $response = $this->view('fingerprint/devices', [
            'devices' => $devices,
            'activeCount' => $activeCount,
            'lastSyncSummary' => $summary,
            'lastSyncMap' => $lastSyncMap,
            'csrfToken' => ensure_csrf_token(),
            'alert' => flash('fingerprint_alert'),
        ], 'Perangkat Fingerprint');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Fingerprint',
            'Perangkat',
        ];
        $response['breadcrumb_actions'] = [
            [
                'href' => route('fingerprint_devices', ['action' => 'create']),
                'label' => 'Tambah Perangkat',
                'icon' => 'fas fa-plus',
                'variant' => 'primary',
            ],
        ];

        return $response;
    }

    private function create(): array
    {
        $formData = $_SESSION['fingerprint_form_data'] ?? [];
        $errors = $_SESSION['fingerprint_errors'] ?? [];
        unset($_SESSION['fingerprint_form_data'], $_SESSION['fingerprint_errors']);

        $response = $this->view('fingerprint/device_form', [
            'isEdit' => false,
            'device' => $formData,
            'errors' => $errors,
            'csrfToken' => ensure_csrf_token(),
        ], 'Tambah Perangkat Fingerprint');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Fingerprint' => route('fingerprint_devices'),
            'Tambah Perangkat',
        ];

        return $response;
    }

    private function edit(): array
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            flash('fingerprint_alert', 'Perangkat tidak ditemukan.', 'danger');
            redirect(route('fingerprint_devices'));
        }

        $model = new FingerprintDevice();
        $device = $_SESSION['fingerprint_form_data'] ?? $model->find($id);
        $errors = $_SESSION['fingerprint_errors'] ?? [];
        unset($_SESSION['fingerprint_form_data'], $_SESSION['fingerprint_errors']);

        if (!$device) {
            flash('fingerprint_alert', 'Perangkat tidak ditemukan.', 'danger');
            redirect(route('fingerprint_devices'));
        }

        $response = $this->view('fingerprint/device_form', [
            'isEdit' => true,
            'device' => $device,
            'errors' => $errors,
            'csrfToken' => ensure_csrf_token(),
        ], 'Edit Perangkat Fingerprint');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Fingerprint' => route('fingerprint_devices'),
            'Edit Perangkat',
        ];

        return $response;
    }

    private function store(): string
    {
        $this->assertPost();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('fingerprint_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('fingerprint_devices', ['action' => 'create']));
        }

        $data = $this->sanitizeInput($_POST);
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $_SESSION['fingerprint_form_data'] = $data;
            $_SESSION['fingerprint_errors'] = $errors;
            redirect(route('fingerprint_devices', ['action' => 'create']));
        }

        try {
            $model = new FingerprintDevice();
            $model->create($this->mapToDb($data));
            flash('fingerprint_alert', 'Perangkat berhasil ditambahkan.', 'success');
        } catch (PDOException $e) {
            $_SESSION['fingerprint_form_data'] = $data;
            $_SESSION['fingerprint_errors'] = ['Gagal menyimpan data: ' . $e->getMessage()];
            redirect(route('fingerprint_devices', ['action' => 'create']));
        }

        redirect(route('fingerprint_devices'));
    }

    private function update(): string
    {
        $this->assertPost();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('fingerprint_alert', 'Perangkat tidak ditemukan.', 'danger');
            redirect(route('fingerprint_devices'));
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('fingerprint_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('fingerprint_devices', ['action' => 'edit', 'id' => $id]));
        }

        $data = $this->sanitizeInput($_POST);
        $errors = $this->validate($data, true);

        if (!empty($errors)) {
            $_SESSION['fingerprint_form_data'] = $data;
            $_SESSION['fingerprint_errors'] = $errors;
            redirect(route('fingerprint_devices', ['action' => 'edit', 'id' => $id]));
        }

        try {
            $model = new FingerprintDevice();
            $model->update($id, $this->mapToDb($data));
            flash('fingerprint_alert', 'Perangkat berhasil diperbarui.', 'success');
        } catch (PDOException $e) {
            $_SESSION['fingerprint_form_data'] = $data;
            $_SESSION['fingerprint_errors'] = ['Gagal memperbarui data: ' . $e->getMessage()];
            redirect(route('fingerprint_devices', ['action' => 'edit', 'id' => $id]));
        }

        redirect(route('fingerprint_devices'));
    }

    private function delete(): string
    {
        $this->assertPost();

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('fingerprint_alert', 'Perangkat tidak ditemukan.', 'danger');
            redirect(route('fingerprint_devices'));
        }

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('fingerprint_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('fingerprint_devices'));
        }

        try {
            $model = new FingerprintDevice();
            $model->delete($id);
            flash('fingerprint_alert', 'Perangkat berhasil dihapus.', 'success');
        } catch (PDOException $e) {
            flash('fingerprint_alert', 'Gagal menghapus data: ' . $e->getMessage(), 'danger');
        }

        redirect(route('fingerprint_devices'));
    }

    public function logs(): array
    {
        $this->requireRole('admin');

        return $this->buildLogs();
    }

    private function buildLogs(): array
    {
        $logModel = new FingerprintLog();
        $limit = isset($_GET['limit']) ? max(1, (int) $_GET['limit']) : 100;
        $logs = $logModel->recent($limit);

        $response = $this->view('fingerprint/logs', [
            'logs' => $logs,
            'limit' => $limit,
        ], 'Log Fingerprint');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Fingerprint' => route('fingerprint_devices'),
            'Log Perangkat',
        ];

        return $response;
    }

    private function sanitizeInput(array $input): array
    {
        $port = isset($input['port']) && $input['port'] !== '' ? (int) $input['port'] : 4370;

        return [
            'id' => (int) ($input['id'] ?? 0),
            'ip' => trim($input['ip'] ?? ''),
            'port' => $port,
            'nama_lokasi' => trim($input['nama_lokasi'] ?? ''),
            'keterangan' => trim($input['keterangan'] ?? ''),
            'is_active' => isset($input['is_active']) ? 1 : 0,
        ];
    }

    private function validate(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        if ($data['ip'] === '') {
            $errors[] = 'IP address wajib diisi.';
        } elseif (!filter_var($data['ip'], FILTER_VALIDATE_IP)) {
            $errors[] = 'IP address tidak valid.';
        }

        if ($data['port'] <= 0 || $data['port'] > 65535) {
            $errors[] = 'Port harus antara 1-65535.';
        }

        if ($data['nama_lokasi'] === '') {
            $errors[] = 'Nama lokasi wajib diisi.';
        }

        return $errors;
    }

    private function mapToDb(array $data): array
    {
        return [
            'ip' => $data['ip'],
            'port' => $data['port'] ?: 4370,
            'nama_lokasi' => $data['nama_lokasi'],
            'keterangan' => $data['keterangan'] ?: null,
            'is_active' => $data['is_active'] ? 1 : 0,
        ];
    }

    private function assertPost(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Metode tidak diizinkan');
        }
    }

    private function sync(): string
    {
        $this->requireRole('admin');

        $deviceModel = new FingerprintDevice();
        $logModel = new FingerprintLog();
        $devices = $deviceModel->active();

        if (empty($devices)) {
            flash('fingerprint_alert', 'Tidak ada perangkat aktif untuk disinkronkan.', 'warning');
            redirect(route('fingerprint_devices'));
        }

        $totalDevices = count($devices);
        $connected = 0;
        $totalPulled = 0;
        $mappedInserted = 0;

        foreach ($devices as $dev) {
            $ip = $dev['ip'];
            $port = (int)($dev['port'] ?? 4370);

            try {
                $zk = new \ZKLib\ZKLib($ip, $port, 10);
                if (!$zk->connect()) {
                    $logModel->create([
                        'action' => 'attendance.connect',
                        'message' => json_encode(['ip' => $ip, 'port' => $port, 'error' => 'connect_failed'], JSON_UNESCAPED_UNICODE),
                        'status' => 'error',
                    ]);
                    continue;
                }

                $connected++;
                $attendance = $zk->getAttendance();
                if (is_array($attendance)) {
                    foreach ($attendance as $rec) {
                        // Normalize record
                        $uid = $rec['uid'] ?? ($rec[0] ?? null);
                        $userid = $rec['id'] ?? ($rec[1] ?? null);
                        $state = $rec['state'] ?? ($rec[2] ?? null);
                        $ts = $rec['timestamp'] ?? ($rec[3] ?? null);

                        $totalPulled++;
                        $logModel->create([
                            'action' => 'attendance.pull',
                            'message' => json_encode(['ip' => $ip, 'uid' => $uid, 'userid' => $userid, 'state' => $state, 'timestamp' => $ts], JSON_UNESCAPED_UNICODE),
                            'status' => 'success',
                        ]);

                        // Mapping prioritas: fingerprint UID -> guru_fingerprint -> guru.user_id
                        $mapped = false;
                        if ($uid !== null) {
                            $user = $this->resolveUserByFingerprintUid((string)$uid);
                            if ($user) {
                                $this->insertKehadiran((int)$user['id'], $user['name'], $ts, (string)($state ?? ''));
                                $mappedInserted++;
                                $mapped = true;
                            }
                        }

                        // Fallback: userid device cocok dengan users.name
                        if (!$mapped && $userid !== null) {
                            $user = $this->resolveUserByDeviceUserId((string)$userid);
                            if ($user) {
                                $this->insertKehadiran((int)$user['id'], $user['name'], $ts, (string)($state ?? ''));
                                $mappedInserted++;
                            }
                        }
                    }
                }

                // Update last_sync_at for this device after a successful session
                try {
                    $stmtUpd = db()->prepare('UPDATE fingerprint_devices SET last_sync_at = NOW() WHERE ip = :ip AND port = :port');
                    $stmtUpd->execute(['ip' => $ip, 'port' => $port]);
                } catch (Throwable $e) {
                    // log but do not interrupt flow
                    $logModel->create([
                        'action' => 'attendance.last_sync_update_error',
                        'message' => json_encode(['ip' => $ip, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE),
                        'status' => 'error',
                    ]);
                }

                $zk->disconnect();
            } catch (Throwable $e) {
                $logModel->create([
                    'action' => 'attendance.error',
                    'message' => json_encode(['ip' => $ip, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE),
                    'status' => 'error',
                ]);
            }
        }

        flash('fingerprint_alert', sprintf('Sinkronisasi selesai. Device aktif: %d/%d, log ditarik: %d, kehadiran terpetakan: %d', $connected, $totalDevices, $totalPulled, $mappedInserted), 'success');
        redirect(route('fingerprint_devices'));
    }

    private function resolveUserByFingerprintUid(string $uid): ?array
    {
        try {
            // Try guru mapping first
            $sqlGuru = "SELECT u.*
                    FROM guru_fingerprint gf
                    JOIN guru g ON g.id_guru = gf.id_guru
                    JOIN users u ON u.id = g.user_id
                    WHERE gf.fingerprint_uid = :uid
                    LIMIT 1";
            $stmt = db()->prepare($sqlGuru);
            $stmt->execute(['uid' => $uid]);
            $row = $stmt->fetch();
            if ($row) {
                return $row;
            }

            // Fallback to siswa mapping
            $sqlSis = "SELECT u.*
                    FROM siswa_fingerprint sf
                    JOIN siswa s ON s.id_siswa = sf.id_siswa
                    JOIN users u ON u.id = s.user_id
                    WHERE sf.fingerprint_uid = :uid
                    LIMIT 1";
            $stmt = db()->prepare($sqlSis);
            $stmt->execute(['uid' => $uid]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (Throwable $e) {
            // log but do not interrupt flow
            $logModel = new FingerprintLog();
            $logModel->create([
                'action' => 'resolve_user_by_fingerprint_uid.error',
                'message' => json_encode(['uid' => $uid, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE),
                'status' => 'error',
            ]);
            return null;
        }
    }

    private function resolveUserByDeviceUserId(string $userid): ?array
    {
        try {
            $userModel = new User();
            return $userModel->findByName($userid);
        } catch (Throwable $e) {
            // log but do not interrupt flow
            $logModel = new FingerprintLog();
            $logModel->create([
                'action' => 'resolve_user_by_device_user_id.error',
                'message' => json_encode(['userid' => $userid, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE),
                'status' => 'error',
            ]);
            return null;
        }
    }

    private function insertKehadiran(int $userId, string $userName, ?string $timestamp, string $status): void
    {
        try {
            $stmt = db()->prepare('INSERT INTO tbl_kehadiran (user_id, user_name, timestamp, status) VALUES (:uid, :name, :ts, :status)');
            $stmt->execute([
                'uid' => $userId,
                'name' => $userName,
                'ts' => $timestamp ?: date('Y-m-d H:i:s'),
                'status' => $status,
            ]);
        } catch (Throwable $e) {
            // log but do not interrupt flow
            $logModel = new FingerprintLog();
            $logModel->create([
                'action' => 'insert_kehadiran.error',
                'message' => json_encode(['userid' => $userId, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE),
                'status' => 'error',
            ]);
        }
    }

    public function uids_siswa(): array
    {
        $this->requireRole('admin');
        $mappings = $this->fetchSiswaUidMappings();
        $siswaModel = new Siswa();
        $siswas = $siswaModel->all();

        $uidOptions = $this->collectRecentUids(1000);

        $response = $this->view('fingerprint/siswa_uid', [
            'mappings' => $mappings,
            'siswas' => $siswas,
            'uidOptions' => $uidOptions,
            'csrfToken' => ensure_csrf_token(),
            'alert' => flash('fingerprint_alert'),
        ], 'Mapping UID Fingerprint Siswa');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Fingerprint' => route('fingerprint_devices'),
            'Mapping UID Siswa',
        ];
        return $response;
    }

    public function store_uid_siswa(): void
    {
        $this->requireRole('admin');
        $this->assertPost();
        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('fingerprint_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('fingerprint_devices', ['action' => 'uids_siswa']));
        }
        $idSiswa = (int)($_POST['id_siswa'] ?? 0);
        $uid = trim($_POST['fingerprint_uid'] ?? '');
        $deviceSerial = trim($_POST['device_serial'] ?? '');
        $errors = [];
        if ($idSiswa <= 0) { $errors[] = 'Siswa wajib dipilih.'; }
        if ($uid === '') { $errors[] = 'UID fingerprint wajib diisi.'; }
        if (!empty($errors)) {
            flash('fingerprint_alert', implode(' ', $errors), 'danger');
            redirect(route('fingerprint_devices', ['action' => 'uids_siswa']));
        }
        try {
            $stmt = db()->prepare('INSERT INTO siswa_fingerprint (id_siswa, fingerprint_uid, device_serial, created_at, updated_at) VALUES (:id_siswa, :uid, :serial, NOW(), NOW()) ON DUPLICATE KEY UPDATE device_serial = VALUES(device_serial), updated_at = NOW()');
            $stmt->execute([
                'id_siswa' => $idSiswa,
                'uid' => $uid,
                'serial' => $deviceSerial !== '' ? $deviceSerial : null,
            ]);
            flash('fingerprint_alert', 'Mapping UID siswa berhasil disimpan.', 'success');
        } catch (Throwable $e) {
            flash('fingerprint_alert', 'Gagal menyimpan mapping siswa: ' . $e->getMessage(), 'danger');
        }
        redirect(route('fingerprint_devices', ['action' => 'uids_siswa']));
    }

    public function delete_uid_siswa(): void
    {
        $this->requireRole('admin');
        $this->assertPost();
        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('fingerprint_alert', 'Token tidak valid.', 'danger');
            redirect(route('fingerprint_devices', ['action' => 'uids_siswa']));
        }
        $idSiswa = (int)($_POST['id_siswa'] ?? 0);
        $uid = trim($_POST['fingerprint_uid'] ?? '');
        if ($idSiswa <= 0 || $uid === '') {
            flash('fingerprint_alert', 'Param tidak valid.', 'danger');
            redirect(route('fingerprint_devices', ['action' => 'uids_siswa']));
        }
        try {
            $stmt = db()->prepare('DELETE FROM siswa_fingerprint WHERE id_siswa = :id_siswa AND fingerprint_uid = :uid');
            $stmt->execute(['id_siswa' => $idSiswa, 'uid' => $uid]);
            flash('fingerprint_alert', 'Mapping UID siswa dihapus.', 'success');
        } catch (Throwable $e) {
            flash('fingerprint_alert', 'Gagal menghapus mapping siswa: ' . $e->getMessage(), 'danger');
        }
        redirect(route('fingerprint_devices', ['action' => 'uids_siswa']));
    }

    private function fetchSiswaUidMappings(): array
    {
        try {
            $sql = 'SELECT sf.*, s.nama_siswa FROM siswa_fingerprint sf JOIN siswa s ON s.id_siswa = sf.id_siswa ORDER BY s.nama_siswa';
            return db()->query($sql)->fetchAll();
        } catch (Throwable $e) {
            // log but do not interrupt flow
            $logModel = new FingerprintLog();
            $logModel->create([
                'action' => 'fetch_siswa_uid_mappings.error',
                'message' => json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE),
                'status' => 'error',
            ]);
            return [];
        }
    }

    public function uids(): array
    {
        $this->requireRole('admin');

        $mappings = $this->fetchGuruUidMappings();

        // Use compact guru options list
        $gurus = [];
        try {
            $guruModel = new Guru();
            if (method_exists($guruModel, 'options')) {
                $gurus = $guruModel->options();
            } else {
                // Fallback minimal data
                $gurus = array_map(static function ($g) {
                    return ['id_guru' => $g['id_guru'], 'nama_guru' => $g['nama_guru']];
                }, $guruModel->allWithUser());
            }
        } catch (Throwable $e) {
            $gurus = [];
        }

        $uidOptions = $this->collectRecentUids(1000);

        $response = $this->view('fingerprint/guru_uid', [
            'mappings' => $mappings,
            'gurus' => $gurus,
            'uidOptions' => $uidOptions,
            'csrfToken' => ensure_csrf_token(),
            'alert' => flash('fingerprint_alert'),
        ], 'Mapping UID Fingerprint');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Fingerprint' => route('fingerprint_devices'),
            'Mapping UID',
        ];

        return $response;
    }

    public function store_uid(): void
    {
        $this->requireRole('admin');
        $this->assertPost();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('fingerprint_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('fingerprint_devices', ['action' => 'uids']));
        }

        $idGuru = (int) ($_POST['id_guru'] ?? 0);
        $uid = trim($_POST['fingerprint_uid'] ?? '');
        $deviceSerial = trim($_POST['device_serial'] ?? '');

        $errors = [];
        if ($idGuru <= 0) { $errors[] = 'Guru wajib dipilih.'; }
        if ($uid === '') { $errors[] = 'UID fingerprint wajib diisi.'; }

        if (!empty($errors)) {
            flash('fingerprint_alert', implode(' ', $errors), 'danger');
            redirect(route('fingerprint_devices', ['action' => 'uids']));
        }

        try {
            $stmt = db()->prepare('INSERT INTO guru_fingerprint (id_guru, fingerprint_uid, device_serial, created_at, updated_at) VALUES (:id_guru, :uid, :serial, NOW(), NOW()) ON DUPLICATE KEY UPDATE device_serial = VALUES(device_serial), updated_at = NOW()');
            $stmt->execute([
                'id_guru' => $idGuru,
                'uid' => $uid,
                'serial' => $deviceSerial !== '' ? $deviceSerial : null,
            ]);
            flash('fingerprint_alert', 'Mapping UID berhasil disimpan.', 'success');
        } catch (Throwable $e) {
            flash('fingerprint_alert', 'Gagal menyimpan mapping: ' . $e->getMessage(), 'danger');
        }

        redirect(route('fingerprint_devices', ['action' => 'uids']));
    }

    public function delete_uid(): void
    {
        $this->requireRole('admin');
        $this->assertPost();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('fingerprint_alert', 'Token tidak valid.', 'danger');
            redirect(route('fingerprint_devices', ['action' => 'uids']));
        }

        $idGuru = (int) ($_POST['id_guru'] ?? 0);
        $uid = trim($_POST['fingerprint_uid'] ?? '');

        if ($idGuru <= 0 || $uid === '') {
            flash('fingerprint_alert', 'Param tidak valid.', 'danger');
            redirect(route('fingerprint_devices', ['action' => 'uids']));
        }

        try {
            $stmt = db()->prepare('DELETE FROM guru_fingerprint WHERE id_guru = :id_guru AND fingerprint_uid = :uid');
            $stmt->execute(['id_guru' => $idGuru, 'uid' => $uid]);
            flash('fingerprint_alert', 'Mapping UID dihapus.', 'success');
        } catch (Throwable $e) {
            flash('fingerprint_alert', 'Gagal menghapus mapping: ' . $e->getMessage(), 'danger');
        }

        redirect(route('fingerprint_devices', ['action' => 'uids']));
    }

    private function fetchGuruUidMappings(): array
    {
        try {
            $sql = 'SELECT gf.*, g.nama_guru FROM guru_fingerprint gf JOIN guru g ON g.id_guru = gf.id_guru ORDER BY g.nama_guru';
            return db()->query($sql)->fetchAll();
        } catch (Throwable $e) {
            // log but do not interrupt flow
            $logModel = new FingerprintLog();
            $logModel->create([
                'action' => 'fetch_guru_uid_mappings.error',
                'message' => json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE),
                'status' => 'error',
            ]);
            return [];
        }
    }

    private function collectRecentUids(int $limit = 1000): array
    {
        try {
            $stmt = db()->prepare("SELECT message FROM fingerprint_logs WHERE action = 'attendance.pull' ORDER BY created_at DESC LIMIT :lim");
            $stmt->bindValue(':lim', max(1, (int)$limit), PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll() ?: [];
            $uids = [];
            foreach ($rows as $r) {
                $msg = $r['message'] ?? '';
                $data = json_decode($msg, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                    $uid = $data['uid'] ?? null;
                    if ($uid !== null && $uid !== '') {
                        $uids[(string)$uid] = true;
                    }
                }
            }
            $list = array_keys($uids);
            sort($list, SORT_NATURAL);
            return $list;
        } catch (Throwable $e) {
            return [];
        }
    }
}
