# Panduan Setup dan Testing Lengkap - Manajemen Sekolah

## 🎯 **Status Setup Saat Ini**

### ✅ **Yang Sudah Selesai**
- PHP 8.0.0 terinstall dan berfungsi
- MySQL 8.0.30 terinstall dan tersedia
- Composer 2.8.4 terinstall
- Dependencies composer sudah diupdate dan compatible
- Struktur aplikasi lengkap dan syntax-valid

### ⚠️ **Yang Perlu Dilakukan Manual**

## 📋 **Langkah-langkah Setup Database**

### 1. **Buat Database**
```sql
CREATE DATABASE dpgwgcvf_salassika CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. **Import Schema Database**
- Buka phpMyAdmin atau MySQL command line
- Import file `dpgwgcvf_salassika.sql`
- Atau jalankan: `mysql -u root dpgwgcvf_salassika < dpgwgcvf_salassika.sql`

### 3. **Verifikasi Database**
```sql
USE dpgwgcvf_salassika;
SHOW TABLES;
```

## 🚀 **Testing Aplikasi**

### **Opsi 1: Menggunakan XAMPP**
1. Start Apache dan MySQL di XAMPP Control Panel
2. Akses: `http://localhost/managemen_sekolah/public/index.php`

### **Opsi 2: Menggunakan PHP Built-in Server**
```bash
cd c:\xampp\htdocs\managemen_sekolah
php -S localhost:8080 -t public
```
Akses: `http://localhost:8080/index.php`

## 🔑 **Konfigurasi API Keys (Opsional)**

### **WhatsApp Integration (Fonnte)**
1. Daftar di https://fonnte.com
2. Edit `.env` file:
```env
FONNTE_API_KEY=your_api_key_here
FONNTE_DEVICE_ID=your_device_id_here
```

### **Fingerprint Integration (ZKTeco)**
1. Setup perangkat ZKTeco X100-C
2. Edit konfigurasi di menu "Fingerprint > Devices"

## 🧪 **Testing Manual**

### **1. Test Autentikasi**
- Akses halaman login
- Login dengan akun: `admin` (password ada di database tabel users)
- Test role-based access control

### **2. Test CRUD Operations**
- **Guru**: Tambah, edit, hapus data guru
- **Siswa**: Tambah, edit, hapus data siswa
- **Kelas**: Kelola data kelas dan jurusan
- **Jadwal**: Setup jadwal pelajaran

### **3. Test Sistem Absensi**
- Input absensi guru dan siswa
- Test filter berdasarkan tanggal/kelas
- Export data ke CSV

### **4. Test Laporan**
- Generate laporan absensi
- Test grafik dan statistik
- Export laporan ke PDF/Excel

### **5. Test Integrasi**
- **Fingerprint**: Setup device dan test sync
- **WhatsApp**: Test kirim notifikasi
- **Import/Export**: Test bulk import data

## 📊 **Monitoring & Health Check**

Setelah setup selesai, akses:
- `http://localhost:8080/index.php?page=health` (untuk monitoring sistem)
- `http://localhost:8080/index.php?page=dashboard` (untuk overview)

## 🔧 **Troubleshooting**

### **Database Connection Issues**
- Pastikan MySQL service running
- Check kredensial di `.env` file
- Verifikasi database `dpgwgcvf_salassika` sudah dibuat

### **Permission Issues**
- Pastikan folder `public/uploads/` writable
- Check permission file untuk web server

### **PHP Errors**
- Enable error reporting di php.ini
- Check logs di `storage/logs/`

## ✅ **Checklist Setup Lengkap**

- [ ] Database dibuat dan schema diimport
- [ ] Aplikasi dapat diakses via browser
- [ ] Login berhasil dengan akun admin
- [ ] Semua menu dapat diakses
- [ ] CRUD operations berfungsi
- [ ] Export features berfungsi
- [ ] API integrations dikonfigurasi (opsional)

## 🎉 **Setup Selesai!**

Setelah semua langkah di atas selesai, aplikasi **Manajemen Sekolah** sudah siap digunakan untuk:
- ✅ Manajemen data guru, siswa, kelas
- ✅ Sistem absensi dan laporan
- ✅ Integrasi fingerprint dan WhatsApp
- ✅ Portal siswa dan monitoring admin
