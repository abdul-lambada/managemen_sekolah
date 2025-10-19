-- =============================================
-- SETUP DATABASE AMAN - MANAJEMEN SEKOLAH
-- =============================================
-- Script ini akan membuat semua tabel dengan aman
-- Menggunakan DROP TABLE IF EXISTS untuk menghindari error duplikasi
-- =============================================

-- 1. Pastikan database sudah dibuat
CREATE DATABASE IF NOT EXISTS dpgwgcvf_salassika CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dpgwgcvf_salassika;

-- =============================================
-- 2. HAPUS TABEL YANG SUDAH ADA (AMAN)
-- =============================================

DROP TABLE IF EXISTS `whatsapp_webhook_logs`;
DROP TABLE IF EXISTS `whatsapp_templates`;
DROP TABLE IF EXISTS `whatsapp_rate_limits`;
DROP TABLE IF EXISTS `whatsapp_message_templates`;
DROP TABLE IF EXISTS `whatsapp_logs`;
DROP TABLE IF EXISTS `whatsapp_device_status`;
DROP TABLE IF EXISTS `whatsapp_config`;
DROP TABLE IF EXISTS `whatsapp_automation_logs`;
DROP TABLE IF EXISTS `whatsapp_automation_config`;
DROP TABLE IF EXISTS `vw_whatsapp_stats`;
DROP TABLE IF EXISTS `vw_recent_whatsapp_logs`;
DROP TABLE IF EXISTS `vw_active_templates`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `tbl_kehadiran`;
DROP TABLE IF EXISTS `tbl_jam_kerja`;
DROP TABLE IF EXISTS `system_stats`;
DROP TABLE IF EXISTS `siswa`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `performance_metrics`;
DROP TABLE IF EXISTS `pengaduan`;
DROP TABLE IF EXISTS `maintenance_logs`;
DROP TABLE IF EXISTS `laporan_absensi`;
DROP TABLE IF EXISTS `login_attempts`;
DROP TABLE IF EXISTS `kelas`;
DROP TABLE IF EXISTS `jurusan`;
DROP TABLE IF EXISTS `guru`;
DROP TABLE IF EXISTS `fingerprint_logs`;
DROP TABLE IF EXISTS `fingerprint_devices`;
DROP TABLE IF EXISTS `cache_metadata`;
DROP TABLE IF EXISTS `backup_logs`;
DROP TABLE IF EXISTS `activity_logs`;

-- =============================================
-- 3. STORED PROCEDURES
-- =============================================

DELIMITER $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_clean_rate_limits` ()   BEGIN
    DELETE FROM whatsapp_rate_limits
    WHERE window_end < NOW();
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_send_whatsapp_message` (IN `p_phone_number` VARCHAR(20), IN `p_message` TEXT, IN `p_message_type` ENUM('text','template','image','document','video','audio','button','list'), IN `p_template_name` VARCHAR(100))   BEGIN
    DECLARE v_log_id INT;
    DECLARE v_config_id INT;

        SELECT id INTO v_config_id FROM whatsapp_config LIMIT 1;

        INSERT INTO whatsapp_logs (phone_number, message, message_type, template_name, status)
    VALUES (p_phone_number, p_message, p_message_type, p_template_name, 'pending');

    SET v_log_id = LAST_INSERT_ID();

        SELECT v_log_id as log_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_update_message_status` (IN `p_log_id` INT, IN `p_status` VARCHAR(20), IN `p_message_id` VARCHAR(100), IN `p_response` TEXT)   BEGIN
    UPDATE whatsapp_logs
    SET
        status = p_status,
        message_id = COALESCE(p_message_id, message_id),
        response = COALESCE(p_response, response),
        sent_at = CASE WHEN p_status = 'sent' THEN NOW() ELSE sent_at END,
        delivered_at = CASE WHEN p_status = 'delivered' THEN NOW() ELSE delivered_at END,
        read_at = CASE WHEN p_status = 'read' THEN NOW() ELSE read_at END
    WHERE id = p_log_id;
END$$

DELIMITER ;

-- =============================================
-- 4. BUAT SEMUA TABEL
-- =============================================

--
-- Struktur dari tabel `absensi_guru`
--

CREATE TABLE `absensi_guru` (
  `id_absensi_guru` int(11) NOT NULL,
  `id_guru` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `status_kehadiran` enum('Hadir','Telat','Izin','Sakit','Alfa') NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Struktur dari tabel `absensi_siswa`
--

CREATE TABLE `absensi_siswa` (
  `id_absensi_siswa` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `status_kehadiran` enum('Hadir','Telat','Sakit','Izin','Tidak Hadir') NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Struktur dari tabel `backup_logs`
--

CREATE TABLE `backup_logs` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `type` enum('full','incremental') NOT NULL,
  `size_bytes` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Struktur dari tabel `cache_metadata`
--

CREATE TABLE `cache_metadata` (
  `cache_key` varchar(191) NOT NULL,
  `tags` text DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Struktur dari tabel `fingerprint_devices`
--

CREATE TABLE `fingerprint_devices` (
  `id` int(11) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `port` int(11) DEFAULT 4370,
  `nama_lokasi` varchar(100) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Struktur dari tabel `fingerprint_logs`
--

CREATE TABLE `fingerprint_logs` (
  `id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `status` enum('success','error','warning') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Struktur dari tabel `guru`
--

CREATE TABLE `guru` (
  `id_guru` int(11) NOT NULL,
  `nama_guru` varchar(100) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat` text NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Struktur dari tabel `jurusan`
--

CREATE TABLE `jurusan` (
  `id_jurusan` int(11) NOT NULL,
  `nama_jurusan` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Struktur dari tabel `kelas`
--

CREATE TABLE `kelas` (
  `id_kelas` int(11) NOT NULL,
  `nama_kelas` varchar(50) NOT NULL,
  `id_jurusan` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Struktur dari tabel `laporan_absensi`
--

CREATE TABLE `laporan_absensi` (
  `id_laporan` int(11) NOT NULL,
  `id_absensi_guru` int(11) DEFAULT NULL,
  `id_absensi_siswa` int(11) DEFAULT NULL,
  `periode` enum('Harian','Mingguan','Bulanan') NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_akhir` date NOT NULL,
  `jumlah_hadir` int(11) NOT NULL,
  `jumlah_tidak_hadir` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Struktur dari tabel `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` bigint(20) NOT NULL,
  `username` varchar(100) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Struktur dari tabel `maintenance_logs`
--

CREATE TABLE `maintenance_logs` (
  `id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('success','warning','error') DEFAULT 'success',
  `execution_time` decimal(10,3) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Struktur dari tabel `pengaduan`
--

CREATE TABLE `pengaduan` (
  `id_pengaduan` int(11) NOT NULL,
  `nama_pelapor` varchar(255) NOT NULL,
  `no_wa` varchar(15) DEFAULT NULL,
  `email_pelapor` varchar(255) DEFAULT NULL,
  `role_pelapor` enum('siswa','guru','umum') NOT NULL,
  `kategori` enum('saran','kritik','pembelajaran','organisasi','administrasi','lainnya') NOT NULL,
  `judul_pengaduan` varchar(255) NOT NULL,
  `isi_pengaduan` text NOT NULL,
  `keterangan` text DEFAULT NULL,
  `file_pendukung` varchar(255) DEFAULT NULL,
  `status` enum('pending','diproses','selesai') DEFAULT 'pending',
  `tanggal_pengaduan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Struktur dari tabel `performance_metrics`
--

CREATE TABLE `performance_metrics` (
  `id` int(11) NOT NULL,
  `metrics_data` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `data` mediumtext DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Struktur dari tabel `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` int(11) NOT NULL,
  `nisn` varchar(20) NOT NULL,
  `nama_siswa` varchar(100) NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `alamat` text NOT NULL,
  `id_kelas` int(11) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Struktur dari tabel `system_stats`
--

CREATE TABLE `system_stats` (
  `id` int(11) NOT NULL,
  `stat_key` varchar(100) NOT NULL,
  `stat_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Struktur dari tabel `tbl_jam_kerja`
--

CREATE TABLE `tbl_jam_kerja` (
  `id` int(11) NOT NULL,
  `nama_jam_kerja` varchar(100) NOT NULL,
  `jam_masuk` time NOT NULL,
  `jam_pulang` time NOT NULL,
  `toleransi_telat_menit` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Struktur dari tabel `tbl_kehadiran`
--

CREATE TABLE `tbl_kehadiran` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  `verification_mode` varchar(50) DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `is_processed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: Belum diproses, 1: Sudah diproses',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','guru','siswa') NOT NULL,
  `uid` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Struktur dari tabel `whatsapp_automation_config`
--

CREATE TABLE `whatsapp_automation_config` (
  `id` int(11) NOT NULL,
  `notify_late_arrival` tinyint(1) NOT NULL DEFAULT 1,
  `notify_absence` tinyint(1) NOT NULL DEFAULT 1,
  `notify_parents` tinyint(1) NOT NULL DEFAULT 1,
  `notify_admin` tinyint(1) NOT NULL DEFAULT 1,
  `late_threshold_minutes` int(11) NOT NULL DEFAULT 15,
  `absence_check_time` time NOT NULL DEFAULT '09:00:00',
  `daily_summary_time` time NOT NULL DEFAULT '16:00:00',
  `weekend_notifications` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Struktur dari tabel `whatsapp_automation_logs`
--

CREATE TABLE `whatsapp_automation_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('guru','siswa') NOT NULL,
  `attendance_status` varchar(20) NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `recipient_phone` varchar(20) NOT NULL,
  `recipient_type` enum('user','parent','admin') NOT NULL,
  `template_used` varchar(100) DEFAULT NULL,
  `message_sent` tinyint(1) NOT NULL DEFAULT 0,
  `whatsapp_log_id` int(11) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `attendance_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Struktur dari tabel `whatsapp_config`
--

CREATE TABLE `whatsapp_config` (
  `id` int(11) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `api_url` varchar(255) NOT NULL DEFAULT 'https://api.fonnte.com/send',
  `country_code` varchar(5) NOT NULL DEFAULT '62',
  `device_id` varchar(50) DEFAULT NULL,
  `delay` int(11) NOT NULL DEFAULT 2 COMMENT 'Delay between messages in seconds',
  `retry` int(11) NOT NULL DEFAULT 0 COMMENT 'Number of retries for failed messages',
  `callback_url` varchar(255) DEFAULT NULL,
  `template_language` varchar(10) NOT NULL DEFAULT 'id',
  `webhook_secret` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Struktur dari tabel `whatsapp_device_status`
--

CREATE TABLE `whatsapp_device_status` (
  `id` int(11) NOT NULL,
  `device_id` varchar(50) NOT NULL,
  `status` enum('online','offline','connecting','error') NOT NULL DEFAULT 'offline',
  `last_seen` datetime DEFAULT NULL,
  `battery_level` int(3) DEFAULT NULL,
  `signal_strength` int(3) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Struktur dari tabel `whatsapp_logs`
--

CREATE TABLE `whatsapp_logs` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `message_id` varchar(100) DEFAULT NULL,
  `message_type` enum('text','template','image','document','button','list') NOT NULL DEFAULT 'text',
  `template_name` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('pending','success','failed') NOT NULL DEFAULT 'pending',
  `status_detail` varchar(50) DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `retry_count` int(11) NOT NULL DEFAULT 0,
  `response` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Struktur dari tabel `whatsapp_message_templates`
--

CREATE TABLE `whatsapp_message_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `category` enum('AUTHENTICATION','MARKETING','UTILITY') NOT NULL DEFAULT 'UTILITY',
  `language` varchar(10) NOT NULL DEFAULT 'id',
  `status` enum('PENDING','APPROVED','REJECTED') NOT NULL DEFAULT 'PENDING',
  `template_id` varchar(100) DEFAULT NULL,
  `header` text DEFAULT NULL,
  `body` text NOT NULL,
  `footer` text DEFAULT NULL,
  `variables` text DEFAULT NULL COMMENT 'JSON array of variable names',
  `buttons` text DEFAULT NULL COMMENT 'JSON buttons data',
  `components` text DEFAULT NULL COMMENT 'JSON components data',
  `example` text DEFAULT NULL COMMENT 'JSON example data',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Struktur dari tabel `whatsapp_rate_limits`
--

CREATE TABLE `whatsapp_rate_limits` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `message_type` varchar(50) NOT NULL,
  `count` int(11) NOT NULL DEFAULT 1,
  `window_start` timestamp NOT NULL DEFAULT current_timestamp(),
  `window_end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Struktur dari tabel `whatsapp_templates`
--

CREATE TABLE `whatsapp_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Struktur dari tabel `whatsapp_webhook_logs`
--

CREATE TABLE `whatsapp_webhook_logs` (
  `id` int(11) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `message_id` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `timestamp` varchar(50) DEFAULT NULL,
  `raw_data` longtext DEFAULT NULL,
  `processed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================
-- 5. TAMBAHKAN PRIMARY KEYS & AUTO_INCREMENT
-- =============================================

ALTER TABLE `absensi_guru`
  ADD PRIMARY KEY (`id_absensi_guru`),
  ADD KEY `id_guru` (`id_guru`),
  ADD KEY `idx_absensi_guru_guru_tanggal` (`id_guru`,`tanggal`),
  ADD KEY `idx_absensi_guru_tanggal` (`tanggal`),
  MODIFY `id_absensi_guru` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `absensi_siswa`
  ADD PRIMARY KEY (`id_absensi_siswa`),
  ADD KEY `id_siswa` (`id_siswa`),
  ADD KEY `idx_absensi_siswa_siswa_tanggal` (`id_siswa`,`tanggal`),
  ADD KEY `idx_absensi_siswa_tanggal` (`tanggal`),
  MODIFY `id_absensi_siswa` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `backup_logs`
  ADD PRIMARY KEY (`id`),
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `cache_metadata`
  ADD PRIMARY KEY (`cache_key`),
  ADD KEY `expires_at` (`expires_at`);

ALTER TABLE `fingerprint_devices`
  ADD PRIMARY KEY (`id`),
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `fingerprint_logs`
  ADD PRIMARY KEY (`id`),
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `guru`
  ADD PRIMARY KEY (`id_guru`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD UNIQUE KEY `idx_guru_nip` (`nip`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_guru_user_id` (`user_id`),
  MODIFY `id_guru` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `jurusan`
  ADD PRIMARY KEY (`id_jurusan`),
  MODIFY `id_jurusan` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id_kelas`),
  ADD KEY `id_jurusan` (`id_jurusan`),
  MODIFY `id_kelas` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `laporan_absensi`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_absensi_guru` (`id_absensi_guru`),
  ADD KEY `id_absensi_siswa` (`id_absensi_siswa`),
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `maintenance_logs`
  ADD PRIMARY KEY (`id`),
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pengaduan`
  ADD PRIMARY KEY (`id_pengaduan`),
  MODIFY `id_pengaduan` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `performance_metrics`
  ADD PRIMARY KEY (`id`),
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `last_activity` (`last_activity`);

ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD UNIQUE KEY `nisn` (`nisn`),
  ADD KEY `id_kelas` (`id_kelas`),
  ADD KEY `user_id` (`user_id`),
  MODIFY `id_siswa` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `system_stats`
  ADD PRIMARY KEY (`id`),
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tbl_jam_kerja`
  ADD PRIMARY KEY (`id`),
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tbl_kehadiran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`),
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `whatsapp_automation_config`
  ADD PRIMARY KEY (`id`),
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `whatsapp_automation_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `attendance_date` (`attendance_date`),
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `whatsapp_config`
  ADD PRIMARY KEY (`id`),
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `whatsapp_device_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_id` (`device_id`),
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `whatsapp_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phone_number` (`phone_number`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`),
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `whatsapp_message_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `whatsapp_rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone_message_type` (`phone_number`,`message_type`),
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `whatsapp_templates`
  ADD PRIMARY KEY (`id`),
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `whatsapp_webhook_logs`
  ADD PRIMARY KEY (`id`),
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- =============================================
-- 6. TAMBAHKAN FOREIGN KEY CONSTRAINTS
-- =============================================

ALTER TABLE `absensi_guru`
  ADD CONSTRAINT `absensi_guru_ibfk_1` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `absensi_siswa`
  ADD CONSTRAINT `absensi_siswa_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `guru`
  ADD CONSTRAINT `guru_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `kelas`
  ADD CONSTRAINT `kelas_ibfk_1` FOREIGN KEY (`id_jurusan`) REFERENCES `jurusan` (`id_jurusan`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `laporan_absensi`
  ADD CONSTRAINT `laporan_absensi_ibfk_1` FOREIGN KEY (`id_absensi_guru`) REFERENCES `absensi_guru` (`id_absensi_guru`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `laporan_absensi_ibfk_2` FOREIGN KEY (`id_absensi_siswa`) REFERENCES `absensi_siswa` (`id_absensi_siswa`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `siswa_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `whatsapp_automation_logs`
  ADD CONSTRAINT `whatsapp_automation_logs_ibfk_1` FOREIGN KEY (`whatsapp_log_id`) REFERENCES `whatsapp_logs` (`id`) ON DELETE SET NULL;

-- =============================================
-- 7. TAMBAHKAN DATA SAMPLE
-- =============================================

-- Insert data jurusan
INSERT IGNORE INTO `jurusan` (`id_jurusan`, `nama_jurusan`) VALUES
(1, 'Teknik Komputer dan Jaringan'),
(2, 'Teknik Kendaraan Ringan dan Otomotif'),
(3, 'Akuntansi Keuangan dan Lembaga');

-- Insert data kelas
INSERT IGNORE INTO `kelas` (`id_kelas`, `nama_kelas`, `id_jurusan`) VALUES
(1, 'XI - TKJ 2', 1),
(3, 'XI - TKJ 1', 1);

-- Insert data users (admin)
INSERT IGNORE INTO `users` (`id`, `name`, `phone`, `avatar`, `password`, `role`, `uid`, `created_at`) VALUES
(1, 'admin', NULL, 'uploads/avatar/avatar_1_1753099882.jpg', '$2y$10$MLZxHgbKIYYexDd6Z7NETOiQmqUO9SD1Nd.Tx1PgslwkwSTRoeB86', 'admin', 'null', '2025-03-05 09:07:00');

-- Insert data guru
INSERT IGNORE INTO `guru` (`id_guru`, `nama_guru`, `nip`, `jenis_kelamin`, `tanggal_lahir`, `alamat`, `phone`, `user_id`) VALUES
(8, 'Budi Santoso', '12345678901', 'Laki-laki', '1980-05-15', 'Jl. Merdeka No. 10, Jakarta', NULL, 36);

-- Insert data siswa
INSERT IGNORE INTO `siswa` (`id_siswa`, `nisn`, `nama_siswa`, `jenis_kelamin`, `tanggal_lahir`, `alamat`, `id_kelas`, `nis`, `phone`, `user_id`) VALUES
(2, '3333', 'RICKY', 'Laki-laki', '1990-06-06', 'Majalengka', 3, '1111', NULL, 37);

-- Insert data system_stats
INSERT IGNORE INTO `system_stats` (`id`, `stat_key`, `stat_value`, `updated_at`) VALUES
(1, 'system_version', '1.0.0', '2025-08-07 07:52:00'),
(2, 'last_maintenance', '2025-08-07 14:52:00', '2025-08-07 07:52:00'),
(3, 'total_users', '0', '2025-08-07 07:52:00'),
(4, 'attendance_today', '0', '2025-08-07 07:52:00'),
(5, 'attendance_month', '0', '2025-08-07 07:52:00'),
(6, 'attendance_rate', '0', '2025-08-07 07:52:00'),
(7, 'whatsapp_sent_today', '0', '2025-08-07 07:52:00');

-- Insert data fingerprint_devices
INSERT IGNORE INTO `fingerprint_devices` (`id`, `ip`, `port`, `nama_lokasi`, `keterangan`, `is_active`, `created_at`, `updated_at`) VALUES
(2, '192.168.1.201', 4370, 'Lobby 1', 'Fingerprint Guru', 1, '2025-07-24 10:30:49', '2025-07-24 10:30:49');

-- Insert data tbl_jam_kerja
INSERT IGNORE INTO `tbl_jam_kerja` (`id`, `nama_jam_kerja`, `jam_masuk`, `jam_pulang`, `toleransi_telat_menit`, `created_at`, `updated_at`) VALUES
(1, '', '06:30:00', '15:00:00', 5, '2025-07-28 14:18:04', '2025-07-28 14:19:00');

-- Insert data whatsapp_config
INSERT IGNORE INTO `whatsapp_config` (`id`, `api_key`, `api_url`, `country_code`, `device_id`, `delay`, `retry`, `callback_url`, `template_language`, `webhook_secret`, `updated_at`) VALUES
(1, 'r6QxiHzS8d7zvxbE1bnA', 'https://api.fonnte.com', '62', '6285156553226', 2, 4, '', 'id', '', '2025-08-06 05:45:03');

-- Insert data whatsapp_automation_config
INSERT IGNORE INTO `whatsapp_automation_config` (`id`, `notify_late_arrival`, `notify_absence`, `notify_parents`, `notify_admin`, `late_threshold_minutes`, `absence_check_time`, `daily_summary_time`, `weekend_notifications`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, 5, '06:30:00', '15:00:00', 0, 1, '2025-08-06 11:40:37', '2025-08-06 12:18:03');

-- Insert sample whatsapp_templates
INSERT IGNORE INTO `whatsapp_templates` (`id`, `name`, `message`, `created_at`, `updated_at`) VALUES
(1, 'Pemberitahuan Keterlambatan', 'Yth. Orang Tua/Wali dari {nama},\n\nDiberitahukan bahwa putra/putri Bapak/Ibu terlambat masuk sekolah pada tanggal {tanggal} pukul {waktu}.\n\nMohon bimbingan dan pengawasan dari Bapak/Ibu.\n\nTerima kasih.', '2025-08-05 12:30:18', '2025-08-05 12:30:18'),
(2, 'Pemberitahuan Ketidakhadiran', 'Yth. Orang Tua/Wali dari {nama},\n\nDiberitahukan bahwa putra/putri Bapak/Ibu tidak hadir di sekolah pada tanggal {tanggal} dengan status {status}.\n\nMohon konfirmasi ketidakhadiran putra/putri Bapak/Ibu.\n\nTerima kasih.', '2025-08-05 12:30:18', '2025-08-05 12:30:18');

-- Insert sample whatsapp_message_templates
INSERT IGNORE INTO `whatsapp_message_templates` (`id`, `name`, `display_name`, `category`, `language`, `status`, `template_id`, `header`, `body`, `footer`, `variables`, `buttons`, `components`, `example`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'absensi_berhasil', 'Absensi Berhasil', 'UTILITY', 'id', 'APPROVED', NULL, NULL, 'Halo {{nama}}, absensi Anda pada {{tanggal}} pukul {{waktu}} telah berhasil dicatat dengan status {{status}}. Terima kasih!', NULL, '[\"nama\", \"tanggal\", \"waktu\", \"status\"]', NULL, NULL, NULL, 1, '2025-08-06 04:05:17', '2025-08-06 04:05:17');

-- =============================================
-- 8. BUAT VIEW DAN TRIGGER
-- =============================================

-- View untuk active templates
CREATE OR REPLACE VIEW `vw_active_templates` AS
SELECT
    `id`,
    `name`,
    `display_name`,
    `category`,
    `language`,
    `body`,
    `variables`
FROM `whatsapp_message_templates`
WHERE `is_active` = 1;

-- View untuk recent whatsapp logs
CREATE OR REPLACE VIEW `vw_recent_whatsapp_logs` AS
SELECT
    `id`,
    `phone_number`,
    `message`,
    `message_type`,
    `status`,
    `sent_at`,
    `created_at`,
    CASE
        WHEN `status` = 'success' THEN 'success'
        WHEN `status` = 'failed' THEN 'danger'
        ELSE 'warning'
    END as `status_color`
FROM `whatsapp_logs`
ORDER BY `created_at` DESC
LIMIT 100;

-- View untuk whatsapp stats
CREATE OR REPLACE VIEW `vw_whatsapp_stats` AS
SELECT
    DATE(`created_at`) as `date`,
    `message_type`,
    `status`,
    COUNT(*) as `total_messages`,
    SUM(CASE WHEN `status` = 'success' THEN 1 ELSE 0 END) as `sent_count`,
    SUM(CASE WHEN `status` = 'failed' THEN 1 ELSE 0 END) as `failed_count`,
    SUM(CASE WHEN `status` = 'pending' THEN 1 ELSE 0 END) as `pending_count`
FROM `whatsapp_logs`
GROUP BY DATE(`created_at`), `message_type`, `status`;

-- Trigger untuk whatsapp_config
DROP TRIGGER IF EXISTS `tr_whatsapp_config_update`;
DELIMITER $$
CREATE TRIGGER `tr_whatsapp_config_update` BEFORE UPDATE ON `whatsapp_config` FOR EACH ROW BEGIN
    SET NEW.updated_at = NOW();
END$$
DELIMITER ;

-- Trigger untuk whatsapp_message_templates
DROP TRIGGER IF EXISTS `tr_whatsapp_message_templates_update`;
DELIMITER $$
CREATE TRIGGER `tr_whatsapp_message_templates_update` BEFORE UPDATE ON `whatsapp_message_templates` FOR EACH ROW BEGIN
    SET NEW.updated_at = NOW();
END$$
DELIMITER ;

-- Trigger untuk whatsapp_templates
DROP TRIGGER IF EXISTS `tr_whatsapp_templates_update`;
DELIMITER $$
CREATE TRIGGER `tr_whatsapp_templates_update` BEFORE UPDATE ON `whatsapp_templates` FOR EACH ROW BEGIN
    SET NEW.updated_at = NOW();
END$$
DELIMITER ;

-- =============================================
-- 9. COMMIT TRANSAKSI
-- =============================================

COMMIT;

-- =============================================
-- SETUP DATABASE SELESAI!
-- =============================================
-- Database: dpgwgcvf_salassika sudah siap digunakan
-- Login dengan username: admin
-- =============================================

SELECT 'DATABASE SETUP BERHASIL! 🎉' as status;
