# Manajemen Sekolah

Aplikasi manajemen sekolah berbasis PHP yang dibangun menggunakan template **SB Admin 2**. Sistem mendukung pengelolaan data guru, siswa, kelas, jurusan, absensi, integrasi fingerprint, serta konfigurasi WhatsApp untuk notifikasi.

## Prasyarat

- PHP 8.1 atau lebih baru (uji pada PHP 8.4.12)
- MariaDB/MySQL
- Composer (opsional jika ingin menambah autoload eksternal)
- Server web (XAMPP/Laragon/WAMP) dengan root pada `htdocs`

## Struktur Direktori

- `public/` — entry point (`index.php`), aset SB Admin 2, uploads
- `app/` — inti aplikasi (`controllers/`, `models/`, `core/`)
- `helpers/` — helper global (`app.php`, `flash.php`, `formatter.php`, `view.php`)
- `includes/` — partial layout & bootstrap (`auth.php`, `init.php`, `partials/`)
- `pages/` — view per modul (dashboard, guru, siswa, absensi, fingerprint, whatsapp, dll.)
- `config/` — konfigurasi aplikasi dan koneksi database
- `routes/` — definisi routing sederhana (`web.php`)
- `dpgwgcvf_salassika.sql` — dump database contoh

## Instalasi

1. **Clone/Salin proyek** ke dalam `c:/xampp/htdocs/managemen_sekolah`.
2. **Import database**:
   - Buka phpMyAdmin
   - Buat database baru `dpgwgcvf_salassika`
   - Import file `dpgwgcvf_salassika.sql`
3. **Konfigurasi environment**:
   - Salin `.env.example` menjadi `.env`
   - Sesuaikan nilai berikut:
     ```env
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_NAME=dpgwgcvf_salassika
     DB_USER=root
     DB_PASS=
     APP_URL=http://localhost/managemen_sekolah
     ```
4. **Pastikan permission** untuk folder `public/uploads/`

## Menjalankan Aplikasi

- Jalankan Apache & MySQL pada XAMPP
- Akses `http://localhost/managemen_sekolah/public/index.php`

### Akun Awal

Akun contoh tersedia di tabel `users`. Misal:

- **Admin**: `admin`
- Password terenkripsi di database; set manual jika perlu dengan `password_hash`

## Fitur Utama

- **Dashboard**: ringkasan data, grafik Chart.js, log terbaru.
- **Manajemen Data**: CRUD guru, siswa, kelas, jurusan dengan DataTables & validasi.
- **Absensi**:
  - Absensi guru & siswa (filter tanggal/kelas, ekspor CSV)
  - Laporan absensi (filter periode, ringkasan, grafik, ekspor CSV/PDF/Excel)
  - Laporan keterlambatan guru berbasis jadwal fingerprint (`page=laporan_keterlambatan`)
  - Rekap absensi siswa per kelas (`page=laporan_kelas`) dengan filter kelas & rentang tanggal
- **Fingerprint**: kelola perangkat & lihat log.
- **WhatsApp**: konfigurasi API, log pesan, kelola template.
- **Automasi**: skrip CLI untuk sinkronisasi fingerprint & pengiriman WhatsApp disertai ringkasan status pada `system_stats`.
- **Keamanan**: CSRF token dinamis, hardening sesi (`HttpOnly`, `SameSite=Strict`), reset password berbasis token, dan log aktivitas.
- **Audit**: halaman admin untuk meninjau log aktivitas sistem (`page=activity_logs`).
- **Portal Siswa**: antarmuka khusus role `siswa` untuk memantau absensi & jadwal (`page=portal_siswa`).
- **Import Massal**: modul upload XLSX/CSV (`page=import`) untuk memasukkan data guru, siswa, dan kelas secara bulk.
- **Tampilan Gradien Modern**: latar `body` dan elemen `.bg-gradient-primary` menggunakan gradien ungu–cyan–lime adaptif (`public/assets/css/custom-overrides.css`).
- **Branding Dinamis**: judul halaman, meta description, dan favicon mengikuti pengaturan aplikasi (`app_settings`) baik untuk layout utama maupun halaman publik (`pages/auth/login.php`, `pages/pengaduan/public_form.php`).

## Integrasi WhatsApp (Fonnte)

- Sistem mendukung integrasi API **Fonnte** melalui endpoint default `https://api.fonnte.com`
- Pastikan berikut:
  - `api_key` valid dan berasal dari dashboard Fonnte.
  - `device_id` sesuai perangkat Fonnte yang aktif.
  - `callback_url` diarahkan ke endpoint penerima webhook (jika diperlukan) dan terdaftar di Fonnte.
- Pengiriman bisa diuji dengan menambahkan entri pada `whatsapp_logs` atau menjalankan proses terjadwal eksternal.
- Referensi resmi: [https://fonnte.com/docs](https://fonnte.com/docs)

## Integrasi Fingerprint (X100-C)

- Modul fingerprint menyiapkan manajemen perangkat **ZKTeco X100-C**.
- Data koneksi yang perlu diisi:
  - `ip`: alamat IP mesin fingerprint (misal `192.168.1.201`).
  - `port`: biasanya `4370` untuk ZKTeco.
  - `nama_lokasi` dan `keterangan` sebagai metadata.
  - `is_active` menandakan perangkat siap dipakai.
- Ekspor data ke sistem dilakukan melalui integrasi eksternal (misal script worker) yang membaca dari mesin X100-C menggunakan library **ZKLib** (`composer require totemo/zklib`) dan mendorong catatan ke tabel `tbl_kehadiran` atau `absensi_*`.
- Log integrasi dapat dipantau di halaman **Fingerprint > Log**, termasuk status sukses/gagal.

## Ekspor CSV

Setiap halaman absensi memiliki tombol **Export CSV** yang mempertahankan filter saat ini. Contoh URL:

```
http://localhost/managemen_sekolah/public/index.php?page=absensi_guru&start=2025-07-01&end=2025-07-31&export=csv
```

## Struktur Kode Penting

- **Routing**: `routes/web.php` memetakan `page` ke controller.
- **Controller Base**: `app/core/Controller.php`
- **Model Base**: `app/core/Model.php` (CRUD generik, paginasi, counter)
- **Helpers**:
  - `helpers/app.php` — helper URL, session, CSRF
  - `helpers/view.php` — render layout (default & auth)
  - `helpers/formatter.php` — format tanggal Indonesia, badge status

## Validasi & Pengujian Manual

1. **Login Flow**
   - Coba akses `public/index.php` tanpa login → redirect ke login
   - Masuk sebagai admin, pastikan menu muncul sesuai role

2. **CRUD Guru/Siswa/Kelas/Jurusan**
   - Tambah, edit, hapus data dan pastikan flash message muncul

3. **Absensi**
   - Uji filter tanggal/kelas
   - Klik **Export CSV** dan buka file hasil unduhan

4. **Laporan Absensi**
   - Ganti periode (harian/mingguan/bulanan)
   - Verifikasi total hadir/tidak hadir dan grafik

5. **Fingerprint**
   - Tambah perangkat, ubah status aktif
   - Buka log dengan limit berbeda

6. **WhatsApp**
   - Simpan konfigurasi API (dummy)
   - Tambah/edit template, cek log pesan

## Roadmap Pengembangan

- **Ekspor PDF/Excel**
  - Integrasi library seperti Dompdf atau PhpSpreadsheet.
  - Tambahkan parameter `export=pdf|xlsx` pada `AbsensiGuruController`, `AbsensiSiswaController`, `LaporanAbsensiController`.
  - Gunakan template view khusus untuk rendering PDF dan helper untuk styling.
- **Automasi Terjadwal**
  - Buat skrip CLI (`scripts/whatsapp_dispatch.php`) untuk mengirim pesan pending ke Fonnte.
  - Tambah skrip sinkronisasi fingerprint (`scripts/fingerprint_sync.php`) yang menarik data dari X100-C dan menulis ke tabel absensi.
  - Dokumentasikan penjadwalan menggunakan Windows Task Scheduler atau cron.
- **Monitoring & Alert**
  - Perluas `system_stats` untuk mencatat status terakhir sinkronisasi/dispatch.
  - Kirim notifikasi (email/WhatsApp) otomatis ketika deteksi kegagalan sinkronisasi.

## Automasi CLI

- **Dispatch WhatsApp** (`scripts/whatsapp_dispatch.php`)
  - Jalankan via CLI: `php scripts/whatsapp_dispatch.php --limit=50`
  - Membaca pesan `pending` dari `whatsapp_logs`, mengirim ke API Fonnte, dan menyimpan ringkasan run ke `system_stats` (status `success|warning|idle`).
  - Jadwalkan via:
    - **Windows Task Scheduler**: Action `Start a Program` → `php`, Arguments `c:/xampp/htdocs/managemen_sekolah/scripts/whatsapp_dispatch.php --limit=50`, trigger setiap 5 menit.
    - **cron (Linux)**: `*/5 * * * * /usr/bin/php /var/www/managemen_sekolah/scripts/whatsapp_dispatch.php --limit=50`.
- **Sync Fingerprint X100-C** (`scripts/fingerprint_sync.php`)
  - Jalankan via CLI: `php scripts/fingerprint_sync.php`
  - Menarik log perangkat aktif (`fingerprint_devices`), menyimpan ke `tbl_kehadiran` serta `absensi_guru_mapel`, dan menulis ringkasan ke `system_stats`.
  - Rekomendasi penjadwalan: setiap 2–3 menit (Task Scheduler/cron serupa di atas dengan path skrip fingerprint).

## Monitoring Otomasi

- Dashboard menampilkan kartu status untuk dispatch WhatsApp dan sinkronisasi fingerprint (lihat bagian "Dashboard" di aplikasi).
- Status ditarik dari entri `system_stats` dengan kunci `whatsapp_dispatch_last_run` dan `fingerprint_sync_last_run`.
- Setiap entri berisi ringkasan JSON (timestamp, jumlah sukses/gagal). Gunakan ini untuk setup alert eksternal jika diperlukan.

## Keamanan & Audit

- **Reset Password**: admin/guru dapat meminta reset melalui halaman `Lupa Kata Sandi`. Sistem membuat selector/token dan menampilkan URL reset (tanpa pengiriman email) agar dapat dibagikan manual.
- **Token CSRF Rotasi Otomatis**: `verify_csrf_token()` meregenerasi token setelah setiap validasi untuk mengurangi serangan replay.
- **Hardening Sesi**: cookie sesi di-set `HttpOnly`, `SameSite=Strict`, dan regenerasi ID otomatis pada init.
- **Log Aktivitas**: semua aksi autentikasi penting (login/logout/reset) dicatat pada tabel `activity_logs` dan dapat dipantau via menu `Sistem > Log Aktivitas`.
- **Schema Tambahan**: jalankan skrip `database/security_module.sql` setelah pembaruan untuk menambah tabel `password_resets`, `activity_logs`, dan kolom email pada `users`.

## Portal Siswa

- Pengguna dengan role `siswa` langsung diarahkan ke `page=portal_siswa` setelah login.
- Fitur yang tersedia:
  - **Dashboard**: ringkasan kehadiran 30 hari terakhir, riwayat absensi terbaru.
  - **Absensi Saya**: tabel absensi dengan filter tanggal (`page=portal_siswa_absensi`).
  - **Jadwal Pelajaran**: jadwal kelas yang terhubung (`page=portal_siswa_jadwal`).
- Pastikan setiap siswa yang ingin mengakses portal memiliki relasi `siswa.user_id` ke tabel `users`.

## Import Data Massal

- Buka menu **Manajemen Data > Import Data** (`page=import`).
- Pilih jenis data (`guru`, `siswa`, `kelas`) lalu unggah file `.xlsx` atau `.csv`.
- Baris pertama dianggap header; gunakan nama kolom yang mengikuti panduan di halaman import.
- Sistem akan menampilkan **pratinjau**:
  - Baris valid ditandai hijau, baris bermasalah merah beserta pesan error (kolom wajib kosong, kelas/jurusan tidak ditemukan, format tanggal salah, duplikat NIP/NISN/Nama kelas, dsb.).
  - Hanya baris valid yang akan disimpan saat menekan **Simpan Data Valid**.
- Persyaratan:
  - Pastikan `composer require phpoffice/phpspreadsheet` telah dijalankan jika ingin mengimpor XLSX.
  - Untuk data siswa, nama kelas di file harus sama dengan penamaan kelas pada tabel `kelas`.

## SOP Operasional

- **Backup Database**
  - Masuk ke phpMyAdmin → pilih database → menu **Export** → format `SQL` → klik **Go**.
  - Simpan hasil export harian ke lokasi aman (cloud/offline). Tambahkan prefix tanggal pada nama file (contoh: `backup_YYYYMMDD.sql`).

- **Reset Password Pengguna**
  - Arahkan pengguna ke halaman `Lupa Kata Sandi` (`page=forgot_password`).
  - Masukkan email/nama pengguna → sistem menampilkan URL reset beserta selector/token.
  - Kirimkan URL tersebut secara manual (misal WhatsApp/email). Setelah pengguna mengganti sandi, token otomatis hangus.

- **Penanganan Pengaduan**
  - Data pengaduan diterima via menu publik (`pages/pengaduan/public_form.php`).
  - Admin memonitor melalui menu **Sistem > Pengaduan**.
  - Tindak lanjuti, beri status/komentar, dan catat aktivitas penting di `activity_logs` bila perlu.

- **Diagram Arsitektur Ringkas**
  - Aplikasi berbasis PHP custom MVC: `routes/web.php` → `Controller` → `Model` (PDO) → `pages/` untuk view.
  - Automasi CLI: `scripts/fingerprint_sync.php`, `scripts/whatsapp_dispatch.php` dijalankan terjadwal.
  - Integrasi eksternal: mesin fingerprint (ZKTeco) dan API Fonnte.

## Modul Akademik Lanjutan

- Jalankan skrip `database/jadwal_module.sql` untuk membuat tabel `mata_pelajaran`, `jadwal_pelajaran`, `absensi_guru_mapel`, dan view `v_absensi_guru_terlambat`.
- Menu `Absensi > Jadwal Pelajaran` menyediakan CRUD jadwal & absensi mapel.
- Laporan keterlambatan guru dan rekap siswa per kelas tersedia di submenu `Absensi > Laporan Absensi`.

## Kustomisasi Lanjut

- Tambah middleware lanjutan pada `includes/auth.php`
- Implementasi notifikasi nyata via API Fonnte/Facebook/Twilio
- Export format lain (PDF) bisa menggunakan library tambahan

## Lisensi

Gunakan sesuai kebutuhan internal. Template SB Admin 2 memiliki lisensi MIT dari `StartBootstrap`.
