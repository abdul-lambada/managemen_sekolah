<?php

declare(strict_types=1);

final class ImportController extends Controller
{
    private const ALLOWED_TYPES = ['guru', 'siswa', 'kelas', 'jadwal'];

    private array $typeLabels = [
        'guru' => 'Data Guru',
        'siswa' => 'Data Siswa',
        'kelas' => 'Data Kelas',
        'jadwal' => 'Jadwal Pelajaran',
    ];

    private array $columnInfo = [
        'guru' => [
            ['key' => 'nama_guru', 'label' => 'Nama Guru *'],
            ['key' => 'nip', 'label' => 'NIP *'],
            ['key' => 'password', 'label' => 'Password (opsional, default NIP)'],
            ['key' => 'jenis_kelamin', 'label' => 'Jenis Kelamin (Laki-laki/Perempuan)'],
            ['key' => 'tanggal_lahir', 'label' => 'Tanggal Lahir (YYYY-MM-DD)'],
            ['key' => 'alamat', 'label' => 'Alamat'],
            ['key' => 'phone', 'label' => 'No. Telepon'],
        ],
        'siswa' => [
            ['key' => 'nama_siswa', 'label' => 'Nama Siswa *'],
            ['key' => 'nisn', 'label' => 'NISN *'],
            ['key' => 'nis', 'label' => 'NIS *'],
            ['key' => 'password', 'label' => 'Password (opsional, default NISN)'],
            ['key' => 'jenis_kelamin', 'label' => 'Jenis Kelamin (Laki-laki/Perempuan) *'],
            ['key' => 'kelas', 'label' => 'Nama Kelas *'],
            ['key' => 'tanggal_lahir', 'label' => 'Tanggal Lahir (YYYY-MM-DD)'],
            ['key' => 'alamat', 'label' => 'Alamat'],
            ['key' => 'phone', 'label' => 'No. Telepon'],
        ],
        'kelas' => [
            ['key' => 'nama_kelas', 'label' => 'Nama Kelas *'],
            ['key' => 'jurusan', 'label' => 'Nama Jurusan *'],
        ],
        'jadwal' => [
            ['key' => 'kelas', 'label' => 'Nama Kelas *'],
            ['key' => 'kode_mapel', 'label' => 'Kode Mapel (atau kosong jika pakai nama mapel)'],
            ['key' => 'nama_mapel', 'label' => 'Nama Mapel *'],
            ['key' => 'guru_nip', 'label' => 'NIP Guru (disarankan)'],
            ['key' => 'guru_nama', 'label' => 'Nama Guru (alternatif jika NIP kosong)'],
            ['key' => 'hari', 'label' => "Hari (Senin/Selasa/Rabu/Kamis/Jumat/Sabtu) *"],
            ['key' => 'jam_mulai', 'label' => 'Jam Mulai (HH:MM) *'],
            ['key' => 'jam_selesai', 'label' => 'Jam Selesai (HH:MM) *'],
            ['key' => 'ruang', 'label' => 'Ruang (opsional)'],
            ['key' => 'catatan', 'label' => 'Catatan (opsional)'],
        ],
    ];

    private ?array $kelasCache = null;
    private ?array $jurusanCache = null;

    public function index(): array|string
    {
        $action = $_GET['action'] ?? 'form';

        if ($action === 'template') {
            $this->downloadTemplate();
            exit;
        }

        return match ($action) {
            'upload' => $this->upload(),
            'preview' => $this->preview(),
            'store' => $this->store(),
            default => $this->form(),
        };
    }

    private function mapJadwalRow(array $row, array &$item): void
    {
        $kelasNama = trim($row['kelas'] ?? '');
        if ($kelasNama === '') {
            $item['errors'][] = 'Nama kelas wajib diisi.';
        }
        $kelasId = $kelasNama !== '' ? $this->resolveKelasIdByName($kelasNama) : null;
        if ($kelasNama !== '' && $kelasId === null) {
            $item['errors'][] = 'Kelas dengan nama "' . $kelasNama . '" tidak ditemukan.';
        }

        $kodeMapel = trim($row['kode_mapel'] ?? '');
        $namaMapel = trim($row['nama_mapel'] ?? '');
        if ($namaMapel === '') {
            $item['errors'][] = 'Nama mapel wajib diisi.';
        }
        $mapelId = ($kodeMapel !== '' || $namaMapel !== '') ? $this->resolveMapelId($kodeMapel, $namaMapel) : null;
        if ($namaMapel !== '' && $mapelId === null) {
            $item['errors'][] = 'Mata pelajaran tidak ditemukan (periksa kode/nama).';
        }

        $guruNip = trim($row['guru_nip'] ?? '');
        $guruNama = trim($row['guru_nama'] ?? '');
        $guruId = ($guruNip !== '' || $guruNama !== '') ? $this->resolveGuruId($guruNip, $guruNama) : null;
        if ($guruId === null) {
            $item['errors'][] = 'Guru tidak ditemukan (gunakan NIP atau nama).';
        }

        $hari = trim($row['hari'] ?? '');
        $validDays = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        if (!in_array($hari, $validDays, true)) {
            $item['errors'][] = 'Hari tidak valid. Gunakan: ' . implode('/', $validDays) . '.';
        }

        $jamMulai = trim($row['jam_mulai'] ?? '');
        $jamSelesai = trim($row['jam_selesai'] ?? '');
        if (!$this->isValidTime($jamMulai)) { $item['errors'][] = 'Jam Mulai tidak valid (HH:MM).'; }
        if (!$this->isValidTime($jamSelesai)) { $item['errors'][] = 'Jam Selesai tidak valid (HH:MM).'; }
        if ($this->isValidTime($jamMulai) && $this->isValidTime($jamSelesai) && strtotime($jamMulai) >= strtotime($jamSelesai)) {
            $item['errors'][] = 'Jam Selesai harus lebih besar dari Jam Mulai.';
        }

        // Optional fields
        $ruang = trim($row['ruang'] ?? '');
        $catatan = trim($row['catatan'] ?? '');

        // Overlap check (only if base fields valid)
        if (empty($item['errors'])) {
            $data = [
                'id_kelas' => $kelasId,
                'id_mata_pelajaran' => $mapelId,
                'id_guru' => $guruId,
                'hari' => $hari,
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'ruang' => $ruang ?: null,
                'catatan' => $catatan ?: null,
            ];
            if ($this->hasJadwalOverlap($data)) {
                $item['errors'][] = 'Benturan jadwal (kelas/guru) di hari dan rentang waktu tersebut.';
            }
        }

        $item['data'] = [
            'id_kelas' => $kelasId,
            'id_mata_pelajaran' => $mapelId,
            'id_guru' => $guruId,
            'hari' => $hari,
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'ruang' => $ruang ?: null,
            'catatan' => $catatan ?: null,
        ];
    }

    private function resolveMapelId(string $kode, string $nama): ?int
    {
        if ($kode !== '') {
            $stmt = db()->prepare('SELECT id_mata_pelajaran FROM mata_pelajaran WHERE kode_mapel = :kode LIMIT 1');
            $stmt->execute(['kode' => $kode]);
            $id = $stmt->fetchColumn();
            if ($id) return (int)$id;
        }
        if ($nama !== '') {
            $stmt = db()->prepare('SELECT id_mata_pelajaran FROM mata_pelajaran WHERE nama_mapel = :nama LIMIT 1');
            $stmt->execute(['nama' => $nama]);
            $id = $stmt->fetchColumn();
            if ($id) return (int)$id;
        }
        return null;
    }

    private function resolveGuruId(string $nip, string $nama): ?int
    {
        if ($nip !== '') {
            $stmt = db()->prepare('SELECT id_guru FROM guru WHERE nip = :nip LIMIT 1');
            $stmt->execute(['nip' => $nip]);
            $id = $stmt->fetchColumn();
            if ($id) return (int)$id;
        }
        if ($nama !== '') {
            $stmt = db()->prepare('SELECT id_guru FROM guru WHERE nama_guru = :nama LIMIT 1');
            $stmt->execute(['nama' => $nama]);
            $id = $stmt->fetchColumn();
            if ($id) return (int)$id;
        }
        return null;
    }

    private function hasJadwalOverlap(array $data, ?int $excludeId = null): bool
    {
        $pdo = db();
        $excludeClause = $excludeId ? 'AND id_jadwal <> :exclude' : '';
        // kelas overlap
        $stmt = $pdo->prepare("SELECT COUNT(1) FROM jadwal_pelajaran WHERE hari = :hari AND id_kelas = :kelas {$excludeClause} AND jam_mulai < :end AND :start < jam_selesai");
        $stmt->execute([
            'hari' => $data['hari'],
            'kelas' => (int)$data['id_kelas'],
            'start' => $data['jam_mulai'],
            'end' => $data['jam_selesai'],
            ...( $excludeId ? ['exclude' => (int)$excludeId] : [] ),
        ]);
        if ((int)$stmt->fetchColumn() > 0) return true;
        // guru overlap
        $stmt = $pdo->prepare("SELECT COUNT(1) FROM jadwal_pelajaran WHERE hari = :hari AND id_guru = :guru {$excludeClause} AND jam_mulai < :end AND :start < jam_selesai");
        $stmt->execute([
            'hari' => $data['hari'],
            'guru' => (int)$data['id_guru'],
            'start' => $data['jam_mulai'],
            'end' => $data['jam_selesai'],
            ...( $excludeId ? ['exclude' => (int)$excludeId] : [] ),
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function insertJadwal(array $data): void
    {
        $model = new Jadwal();
        $model->create([
            'id_kelas' => $data['id_kelas'],
            'id_mata_pelajaran' => $data['id_mata_pelajaran'],
            'id_guru' => $data['id_guru'],
            'hari' => $data['hari'],
            'jam_mulai' => $data['jam_mulai'],
            'jam_selesai' => $data['jam_selesai'],
            'ruang' => $data['ruang'],
            'catatan' => $data['catatan'],
        ]);
    }

    private function isValidTime(?string $time): bool
    {
        if (!$time) { return false; }
        $dt = DateTime::createFromFormat('H:i', $time);
        return $dt !== false && $dt->format('H:i') === $time;
    }

    private function downloadTemplate(): void
    {
        $this->requireRole('admin');

        $type = $_GET['type'] ?? '';
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            http_response_code(400);
            echo 'Jenis template tidak dikenali.';
            return;
        }

        $templates = [
            'guru' => [
                'headers' => ['nama_guru', 'nip', 'password', 'jenis_kelamin', 'tanggal_lahir', 'alamat', 'phone'],
                'row' => ['Budi Santoso', '197812312005011001', 'password123', 'Laki-laki', '1980-01-05', 'Jl. Melati No. 10', '081234567890'],
            ],
            'siswa' => [
                'headers' => ['nama_siswa', 'nisn', 'nis', 'password', 'jenis_kelamin', 'kelas', 'tanggal_lahir', 'alamat', 'phone'],
                'row' => ['Ani Wijaya', '0065432101', '2024-07', 'pwdAni!', 'Perempuan', 'XII IPA 1', '2007-04-12', 'Jl. Kenanga No. 5', '082198765432'],
            ],
            'kelas' => [
                'headers' => ['nama_kelas', 'jurusan'],
                'row' => ['XII IPA 1', 'Ilmu Pengetahuan Alam'],
            ],
            'jadwal' => [
                'headers' => ['kelas', 'kode_mapel', 'nama_mapel', 'guru_nip', 'guru_nama', 'hari', 'jam_mulai', 'jam_selesai', 'ruang', 'catatan'],
                'row' => ['XII IPA 1', 'MAT101', 'Matematika', '197812312005011001', 'Budi Santoso', 'Senin', '07:00', '08:40', 'R-101', 'Pertemuan awal'],
            ],
        ];

        $payload = $templates[$type];
        $filename = 'template_' . $type . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');
        if ($output === false) {
            echo 'Tidak dapat membuat output.';
            return;
        }

        fprintf($output, "\xEF\xBB\xBF"); // BOM untuk Excel
        fputcsv($output, $payload['headers']);
        fputcsv($output, $payload['row']);
        fclose($output);
    }

    private function form(): array
    {
        $this->requireRole('admin');

        $response = $this->view('import/index', [
            'mode' => 'form',
            'typeOptions' => $this->typeLabels,
            'columnInfo' => $this->columnInfo,
            'csrfToken' => ensure_csrf_token(),
            'alert' => flash('import_alert'),
        ], 'Import Data');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Import Data'
        ];

        return $response;
    }

    private function upload(): array|string
    {
        $this->requireRole('admin');
        $this->assertPost();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('import_alert', 'Token tidak valid. Silakan coba lagi.', 'danger');
            redirect(route('import'));
        }

        $type = $_POST['type'] ?? '';
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            flash('import_alert', 'Jenis data import tidak dikenali.', 'danger');
            redirect(route('import'));
        }

        if (empty($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            flash('import_alert', 'Harap pilih file XLSX atau CSV yang valid.', 'danger');
            redirect(route('import'));
        }

        $file = $_FILES['import_file'];
        $tmpPath = $file['tmp_name'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            flash('import_alert', 'Format file tidak didukung. Gunakan XLSX atau CSV.', 'danger');
            redirect(route('import'));
        }

        try {
            $rawRows = read_spreadsheet_to_array($tmpPath);
        } catch (Throwable $e) {
            flash('import_alert', 'Gagal membaca file: ' . $e->getMessage(), 'danger');
            redirect(route('import'));
        }

        if (empty($rawRows)) {
            flash('import_alert', 'File tidak berisi data.', 'warning');
            redirect(route('import'));
        }

        $processed = $this->processRows($type, $rawRows);
        $counts = $this->computeCounts($processed);

        $_SESSION['import_preview'] = [
            'type' => $type,
            'rows' => $processed,
            'counts' => $counts,
            'uploaded_at' => time(),
            'filename' => $file['name'],
        ];

        redirect(route('import', ['action' => 'preview', 'type' => $type]));
    }

    private function preview(): array
    {
        $this->requireRole('admin');

        $preview = $_SESSION['import_preview'] ?? null;
        $type = $_GET['type'] ?? ($preview['type'] ?? null);

        if (!$preview || !$type || $preview['type'] !== $type) {
            flash('import_alert', 'Tidak ada data pratinjau. Silakan unggah file terlebih dahulu.', 'warning');
            redirect(route('import'));
        }

        $response = $this->view('import/index', [
            'mode' => 'preview',
            'type' => $type,
            'typeLabel' => $this->typeLabels[$type] ?? strtoupper($type),
            'previewRows' => $preview['rows'],
            'counts' => $preview['counts'],
            'filename' => $preview['filename'] ?? null,
            'csrfToken' => ensure_csrf_token(),
            'columnInfo' => $this->columnInfo,
            'alert' => flash('import_alert'),
        ], 'Pratinjau Import');

        $response['breadcrumbs'] = [
            'Dashboard' => route('dashboard'),
            'Import Data' => route('import'),
            'Pratinjau'
        ];

        return $response;
    }

    private function store(): void
    {
        $this->requireRole('admin');
        $this->assertPost();

        if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
            flash('import_alert', 'Token tidak valid. Silakan ulangi pratinjau.', 'danger');
            redirect(route('import'));
        }

        $preview = $_SESSION['import_preview'] ?? null;
        $type = $_POST['type'] ?? ($preview['type'] ?? null);

        if (!$preview || !$type || $preview['type'] !== $type) {
            flash('import_alert', 'Sesi pratinjau sudah berakhir. Mohon unggah ulang file.', 'warning');
            redirect(route('import'));
        }

        $validRows = array_filter($preview['rows'], static fn (array $row): bool => empty($row['errors']));
        if (empty($validRows)) {
            flash('import_alert', 'Tidak ada baris valid untuk diimpor.', 'warning');
            redirect(route('import'));
        }

        $pdo = db();
        $inserted = 0;
        $skipped = 0;
        $duplicates = 0;

        try {
            $pdo->beginTransaction();

            foreach ($validRows as $row) {
                switch ($type) {
                    case 'guru':
                        if ($this->guruExists($row['data']['nip'])) {
                            $duplicates++;
                            continue 2;
                        }
                        $this->insertGuru($row['data']);
                        break;
                    case 'siswa':
                        if ($this->siswaExists($row['data']['nisn'], $row['data']['nis'])) {
                            $duplicates++;
                            continue 2;
                        }
                        $this->insertSiswa($row['data']);
                        break;
                    case 'kelas':
                        if ($this->kelasExists($row['data']['nama_kelas'])) {
                            $duplicates++;
                            continue 2;
                        }
                        $this->insertKelas($row['data']);
                        break;
                    case 'jadwal':
                        // Skip if overlap exists for kelas/guru at same day/time
                        if ($this->hasJadwalOverlap($row['data'])) {
                            $duplicates++; // treat as duplicate/skip
                            continue 2;
                        }
                        $this->insertJadwal($row['data']);
                        break;
                }

                $inserted++;
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            flash('import_alert', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage(), 'danger');
            redirect(route('import', ['action' => 'preview', 'type' => $type]));
        }

        $skipped = count($preview['rows']) - $inserted - $duplicates;

        unset($_SESSION['import_preview']);

        activity_log('import.store', sprintf('Impor %s berhasil: %d disimpan, %d duplikat, %d dilewati.', $type, $inserted, $duplicates, $skipped));

        flash('import_alert', sprintf('Impor %s selesai. Disimpan: %d, Duplikat: %d, Dilewati: %d.', $this->typeLabels[$type] ?? $type, $inserted, $duplicates, $skipped), 'success');
        redirect(route('import'));
    }

    private function processRows(string $type, array $rows): array
    {
        $processed = [];
        $seenKeys = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // header berada di baris 1
            $item = [
                'row_number' => $rowNumber,
                'data' => [],
                'errors' => [],
                'source' => $row,
            ];

            switch ($type) {
                case 'guru':
                    $this->mapGuruRow($row, $item, $seenKeys);
                    break;
                case 'siswa':
                    $this->mapSiswaRow($row, $item, $seenKeys);
                    break;
                case 'kelas':
                    $this->mapKelasRow($row, $item, $seenKeys);
                    break;
                case 'jadwal':
                    $this->mapJadwalRow($row, $item);
                    break;
            }

            $processed[] = $item;
        }

        return $processed;
    }

    private function mapGuruRow(array $row, array &$item, array &$seen): void
    {
        $nama = trim($row['nama_guru'] ?? '');
        $nip = trim($row['nip'] ?? '');
        $password = (string) ($row['password'] ?? '');

        if ($nama === '') {
            $item['errors'][] = 'Nama guru wajib diisi.';
        }

        if ($nip === '') {
            $item['errors'][] = 'NIP wajib diisi.';
        }

        if ($nip !== '') {
            if (isset($seen['guru'][$nip])) {
                $item['errors'][] = 'Duplikat NIP dalam file.';
            }
            $seen['guru'][$nip] = true;
        }

        $gender = $this->normalizeGender($row['jenis_kelamin'] ?? null);
        if (($row['jenis_kelamin'] ?? '') !== '' && $gender === null) {
            $item['errors'][] = 'Jenis kelamin tidak valid (gunakan Laki-laki atau Perempuan).';
        }

        $birthDate = $this->parseDate($row['tanggal_lahir'] ?? null, $error);
        if ($error !== null) {
            $item['errors'][] = $error;
        }

        $item['data'] = [
            'nama_guru' => $nama,
            'nip' => $nip,
            'jenis_kelamin' => $gender,
            'tanggal_lahir' => $birthDate,
            'alamat' => trim($row['alamat'] ?? ''),
            'phone' => $this->sanitizePhone($row['phone'] ?? null),
            'password' => $password !== '' ? $password : $nip,
        ];
    }

    private function mapSiswaRow(array $row, array &$item, array &$seen): void
    {
        $nama = trim($row['nama_siswa'] ?? '');
        $nisn = trim($row['nisn'] ?? '');
        $nis = trim($row['nis'] ?? '');
        $password = (string) ($row['password'] ?? '');

        if ($nama === '') {
            $item['errors'][] = 'Nama siswa wajib diisi.';
        }

        if ($nisn === '') {
            $item['errors'][] = 'NISN wajib diisi.';
        }

        if ($nis === '') {
            $item['errors'][] = 'NIS wajib diisi.';
        }

        if ($nisn !== '') {
            if (isset($seen['siswa'][$nisn])) {
                $item['errors'][] = 'Duplikat NISN dalam file.';
            }
            $seen['siswa'][$nisn] = true;
        }

        $gender = $this->normalizeGender($row['jenis_kelamin'] ?? null);
        if (($row['jenis_kelamin'] ?? '') !== '' && $gender === null) {
            $item['errors'][] = 'Jenis kelamin tidak valid (gunakan Laki-laki atau Perempuan).';
        }

        $kelasNama = trim($row['kelas'] ?? '');
        if ($kelasNama === '') {
            $item['errors'][] = 'Nama kelas wajib diisi.';
        }
        $kelasId = $kelasNama !== '' ? $this->resolveKelasIdByName($kelasNama) : null;
        if ($kelasNama !== '' && $kelasId === null) {
            $item['errors'][] = 'Kelas dengan nama "' . $kelasNama . '" tidak ditemukan.';
        }

        $birthDate = $this->parseDate($row['tanggal_lahir'] ?? null, $error);
        if ($error !== null) {
            $item['errors'][] = $error;
        }

        $item['data'] = [
            'nama_siswa' => $nama,
            'nisn' => $nisn,
            'nis' => $nis,
            'jenis_kelamin' => $gender,
            'tanggal_lahir' => $birthDate,
            'alamat' => trim($row['alamat'] ?? ''),
            'phone' => $this->sanitizePhone($row['phone'] ?? null),
            'id_kelas' => $kelasId,
            'kelas_nama' => $kelasNama,
            'password' => $password !== '' ? $password : $nisn,
        ];
    }

    private function mapKelasRow(array $row, array &$item, array &$seen): void
    {
        $nama = trim($row['nama_kelas'] ?? '');
        $jurusanNama = trim($row['jurusan'] ?? '');

        if ($nama === '') {
            $item['errors'][] = 'Nama kelas wajib diisi.';
        }

        if ($jurusanNama === '') {
            $item['errors'][] = 'Nama jurusan wajib diisi.';
        }

        if ($nama !== '') {
            if (isset($seen['kelas'][$nama])) {
                $item['errors'][] = 'Nama kelas duplikat dalam file.';
            }
            $seen['kelas'][$nama] = true;
        }

        $jurusanId = $jurusanNama !== '' ? $this->resolveJurusanIdByName($jurusanNama) : null;
        if ($jurusanNama !== '' && $jurusanId === null) {
            $item['errors'][] = 'Jurusan "' . $jurusanNama . '" tidak ditemukan.';
        }

        $item['data'] = [
            'nama_kelas' => $nama,
            'id_jurusan' => $jurusanId,
            'jurusan_nama' => $jurusanNama,
        ];
    }

    private function computeCounts(array $rows): array
    {
        $valid = 0;
        $invalid = 0;
        foreach ($rows as $row) {
            if (empty($row['errors'])) {
                $valid++;
            } else {
                $invalid++;
            }
        }

        return [
            'total' => count($rows),
            'valid' => $valid,
            'invalid' => $invalid,
        ];
    }

    private function normalizeGender(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = strtolower(trim($value));
        return match ($value) {
            'l', 'laki-laki', 'laki laki', 'pria' => 'Laki-laki',
            'p', 'perempuan', 'wanita' => 'Perempuan',
            default => null,
        };
    }

    private function parseDate(?string $value, ?string &$error): ?string
    {
        $error = null;

        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);
        $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y'];
        foreach ($formats as $format) {
            $dt = DateTime::createFromFormat($format, $value);
            if ($dt && $dt->format($format) === $value) {
                return $dt->format('Y-m-d');
            }
        }

        $error = 'Format tanggal tidak dikenali: ' . $value;
        return null;
    }

    private function sanitizePhone(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/[^0-9+]/', '', $value);
        return $digits !== '' ? $digits : null;
    }

    private function resolveKelasIdByName(string $name): ?int
    {
        if ($this->kelasCache === null) {
            $kelasModel = new Kelas();
            $this->kelasCache = [];
            foreach ($kelasModel->allWithJurusan() as $kelas) {
                $this->kelasCache[strtolower($kelas['nama_kelas'])] = (int) $kelas['id_kelas'];
            }
        }

        $key = strtolower($name);
        return $this->kelasCache[$key] ?? null;
    }

    private function resolveJurusanIdByName(string $name): ?int
    {
        if ($this->jurusanCache === null) {
            $jurusanModel = new Jurusan();
            $this->jurusanCache = [];
            foreach ($jurusanModel->all() as $jurusan) {
                $this->jurusanCache[strtolower($jurusan['nama_jurusan'])] = (int) $jurusan['id_jurusan'];
            }
        }

        $key = strtolower($name);
        return $this->jurusanCache[$key] ?? null;
    }

    private function guruExists(string $nip): bool
    {
        $stmt = db()->prepare('SELECT id_guru FROM guru WHERE nip = :nip LIMIT 1');
        $stmt->execute(['nip' => $nip]);
        return (bool) $stmt->fetchColumn();
    }

    private function siswaExists(string $nisn, string $nis): bool
    {
        $stmt = db()->prepare('SELECT id_siswa FROM siswa WHERE nisn = :nisn OR nis = :nis LIMIT 1');
        $stmt->execute([
            'nisn' => $nisn,
            'nis' => $nis,
        ]);
        return (bool) $stmt->fetchColumn();
    }

    private function kelasExists(string $namaKelas): bool
    {
        $stmt = db()->prepare('SELECT id_kelas FROM kelas WHERE nama_kelas = :nama LIMIT 1');
        $stmt->execute(['nama' => $namaKelas]);
        return (bool) $stmt->fetchColumn();
    }

    private function insertGuru(array $data): void
    {
        // create or reuse user by name (use NIP as username)
        $userId = $this->createOrGetUser($data['nip'], 'guru', (string) $data['password'], $data['phone']);

        $model = new Guru();
        $model->create([
            'nama_guru' => $data['nama_guru'],
            'nip' => $data['nip'],
            'jenis_kelamin' => $data['jenis_kelamin'],
            'tanggal_lahir' => $data['tanggal_lahir'],
            'alamat' => $data['alamat'],
            'phone' => $data['phone'],
            'user_id' => $userId,
        ]);
    }

    private function insertSiswa(array $data): void
    {
        // create or reuse user by name (use NISN as username)
        $userId = $this->createOrGetUser($data['nisn'], 'siswa', (string) $data['password'], $data['phone']);

        $model = new Siswa();
        $model->create([
            'nama_siswa' => $data['nama_siswa'],
            'nisn' => $data['nisn'],
            'nis' => $data['nis'],
            'jenis_kelamin' => $data['jenis_kelamin'],
            'tanggal_lahir' => $data['tanggal_lahir'],
            'alamat' => $data['alamat'],
            'id_kelas' => $data['id_kelas'],
            'phone' => $data['phone'],
            'user_id' => $userId,
        ]);
    }

    private function createOrGetUser(string $username, string $role, string $passwordPlain, ?string $phone = null): int
    {
        $userModel = new User();
        $existing = $userModel->findByName($username);
        if ($existing) {
            return (int) $existing['id'];
        }

        $hashed = password_hash($passwordPlain !== '' ? $passwordPlain : $username, PASSWORD_BCRYPT);
        // Use base Model::create
        /** @var Model $userModel */
        $userModel->create([
            'name' => $username,
            'phone' => $phone,
            'password' => $hashed,
            'role' => $role,
        ]);

        $created = $userModel->findByName($username);
        return (int) ($created['id'] ?? 0);
    }

    private function insertKelas(array $data): void
    {
        $model = new Kelas();
        $model->create([
            'nama_kelas' => $data['nama_kelas'],
            'id_jurusan' => $data['id_jurusan'],
        ]);
    }

    private function assertPost(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('Metode tidak diizinkan');
        }
    }
}
