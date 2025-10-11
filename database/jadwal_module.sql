-- Jadwal module schema additions

CREATE TABLE IF NOT EXISTS mata_pelajaran (
    id_mata_pelajaran INT AUTO_INCREMENT PRIMARY KEY,
    kode_mapel VARCHAR(20) NOT NULL UNIQUE,
    nama_mapel VARCHAR(100) NOT NULL,
    keterangan TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS guru_fingerprint (
    id_guru INT NOT NULL,
    fingerprint_uid VARCHAR(50) NOT NULL,
    device_serial VARCHAR(100) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_guru, fingerprint_uid),
    INDEX idx_guru_fingerprint_guru (id_guru)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS jadwal_pelajaran (
    id_jadwal INT AUTO_INCREMENT PRIMARY KEY,
    id_kelas INT NOT NULL,
    id_mata_pelajaran INT NOT NULL,
    id_guru INT NOT NULL,
    hari ENUM('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    ruang VARCHAR(50) NULL,
    catatan TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_jadwal_kelas (id_kelas),
    INDEX idx_jadwal_mapel (id_mata_pelajaran),
    INDEX idx_jadwal_guru (id_guru)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS absensi_guru_mapel (
    id_absensi_mapel BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_jadwal INT NOT NULL,
    tanggal DATE NOT NULL,
    status_kehadiran ENUM('Hadir','Izin','Sakit','Alpa','Terlambat') NOT NULL DEFAULT 'Hadir',
    jam_masuk TIME NULL,
    jam_keluar TIME NULL,
    sumber ENUM('fingerprint','manual') NOT NULL DEFAULT 'manual',
    catatan TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_absensi_jadwal (id_jadwal),
    UNIQUE KEY uniq_absensi_jadwal_tanggal (id_jadwal, tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS absensi_guru_mapel_log (
    id_log BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_absensi_mapel BIGINT UNSIGNED NULL,
    id_jadwal INT NOT NULL,
    fingerprint_user_id VARCHAR(50) NULL,
    timestamp DATETIME NOT NULL,
    status ENUM('Masuk','Keluar') NOT NULL,
    payload JSON NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_absensi_mapel_log_absensi (id_absensi_mapel),
    INDEX idx_absensi_mapel_log_jadwal (id_jadwal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Helper view untuk laporan keterlambatan
CREATE OR REPLACE VIEW v_absensi_guru_terlambat AS
SELECT
    agm.id_absensi_mapel,
    j.id_jadwal,
    g.nama_guru,
    mp.nama_mapel,
    k.nama_kelas,
    agm.tanggal,
    agm.jam_masuk,
    agm.jam_keluar,
    agm.status_kehadiran,
    TIMESTAMPDIFF(MINUTE, CONCAT(agm.tanggal, ' ', j.jam_mulai), CONCAT(agm.tanggal, ' ', agm.jam_masuk)) AS menit_terlambat
FROM absensi_guru_mapel agm
JOIN jadwal_pelajaran j ON agm.id_jadwal = j.id_jadwal
JOIN guru g ON j.id_guru = g.id_guru
JOIN mata_pelajaran mp ON j.id_mata_pelajaran = mp.id_mata_pelajaran
JOIN kelas k ON j.id_kelas = k.id_kelas
WHERE agm.status_kehadiran = 'Terlambat';
