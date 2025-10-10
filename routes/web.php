<?php

declare(strict_types=1);

return [
    'dashboard' => [DashboardController::class, 'index'],
    'login' => [AuthController::class, 'showLogin'],
    'do_login' => [AuthController::class, 'login'],
    'logout' => [AuthController::class, 'logout'],
    'guru' => [GuruController::class, 'index'],
    'siswa' => [SiswaController::class, 'index'],
    'kelas' => [KelasController::class, 'index'],
    'jurusan' => [JurusanController::class, 'index'],
    'absensi_guru' => [AbsensiGuruController::class, 'index'],
    'absensi_siswa' => [AbsensiSiswaController::class, 'index'],
    'laporan_absensi' => [LaporanAbsensiController::class, 'index'],
    'fingerprint_devices' => [FingerprintController::class, 'index'],
    'fingerprint_logs' => [FingerprintController::class, 'logs'],
    'whatsapp_config' => [WhatsAppController::class, 'index'],
    'whatsapp_logs' => [WhatsAppController::class, 'logs'],
    'automation' => [AutomationController::class, 'index'],
    'automation_trigger' => [AutomationController::class, 'trigger'],
    'profile' => [ProfileController::class, 'index'],
    'profile_edit' => [ProfileController::class, 'edit'],
    'profile_update' => [ProfileController::class, 'update'],
    'settings' => [SettingsController::class, 'index'],
    'settings_update' => [SettingsController::class, 'update'],
    'system_stats' => [SystemStatsController::class, 'index'],
    'pengaduan' => [PengaduanController::class, 'index'],
    'pengaduan_form' => [PublicPengaduanController::class, 'form'],
    'pengaduan_submit' => [PublicPengaduanController::class, 'submit'],
];
