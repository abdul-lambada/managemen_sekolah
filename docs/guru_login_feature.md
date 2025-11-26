# Fitur Auto-Create User Account untuk Guru

## Perubahan yang Dilakukan

### 1. Modifikasi GuruController::store()

File: `app/controllers/GuruController.php`

**Fitur Baru:**
- ✅ Otomatis membuat user account saat membuat guru baru
- ✅ Username: `guru_[NIP]` (contoh: `guru_123456789`)
- ✅ Password default: `guru123`
- ✅ Role otomatis: `guru`
- ✅ Menggunakan database transaction untuk keamanan
- ✅ Flash message menampilkan kredensial login

**Alur Proses:**
```
1. Admin mengisi form tambah guru
2. Sistem validasi data
3. Sistem cek apakah user_id sudah dipilih:
   - Jika TIDAK: Buat user account otomatis
   - Jika YA: Gunakan user_id yang dipilih
4. Simpan data guru dengan user_id
5. Tampilkan pesan sukses dengan kredensial login
```

**Contoh Output:**
```
Data guru Ahmad Wijaya berhasil ditambahkan.
Username: guru_123456789
Password default: guru123
Silakan informasikan kredensial ini kepada guru yang bersangkutan.
```

### 2. Script SQL untuk Guru yang Sudah Ada

File: `database/create_user_for_existing_guru.sql`

**Fungsi:**
- Membuat user account untuk guru yang sudah ada di database
- Menggunakan stored procedure untuk otomatis proses
- Password default: `guru123` (sudah di-hash)

**Cara Menggunakan:**

#### Via phpMyAdmin:
1. Login ke phpMyAdmin
2. Pilih database aplikasi
3. Buka tab "SQL"
4. Copy-paste isi file `create_user_for_existing_guru.sql`
5. Klik "Go" atau "Execute"
6. Lihat hasil di output

#### Via MySQL Command Line:
```bash
mysql -u username -p database_name < database/create_user_for_existing_guru.sql
```

## Cara Login Guru

### Untuk Guru Baru (Dibuat Setelah Update):
```
URL: https://manajemen-salassika.akarsekawan.my.id/public/index.php?page=login

Username: guru_[NIP]
Password: guru123

Contoh:
Username: guru_123456789
Password: guru123
```

### Untuk Guru Lama (Sudah Ada Sebelum Update):
1. Jalankan script SQL `create_user_for_existing_guru.sql`
2. Login dengan kredensial yang sama seperti di atas

## Keamanan

### Password Default
- Password default: `guru123`
- **PENTING**: Guru harus mengganti password setelah login pertama kali
- Gunakan fitur "Profile" → "Ubah Password" (jika ada)

### Username Unik
- Sistem otomatis cek duplikasi username
- Jika username sudah ada, akan ditambahkan suffix random
- Contoh: `guru_123456789_a1b2`

### Transaction Safety
- Menggunakan database transaction
- Jika gagal membuat user, data guru tidak akan tersimpan
- Rollback otomatis jika terjadi error

## Testing

### Test 1: Buat Guru Baru
1. Login sebagai admin
2. Menu "Data Guru" → "Tambah Guru"
3. Isi form (tanpa memilih user_id)
4. Submit
5. Cek pesan sukses (harus ada username & password)
6. Coba login dengan kredensial tersebut

### Test 2: Guru yang Sudah Ada
1. Jalankan script SQL
2. Cek tabel `users` (harus ada user baru dengan role 'guru')
3. Cek tabel `guru` (field `user_id` harus terisi)
4. Coba login dengan `guru_[NIP]` dan password `guru123`

### Test 3: Duplikasi Username
1. Buat guru dengan NIP yang sama (seharusnya error di validasi)
2. Atau manual insert user dengan username `guru_123456789`
3. Buat guru baru dengan NIP `123456789`
4. Username harus jadi `guru_123456789_xxxx` (dengan suffix random)

## Troubleshooting

### Guru Tidak Bisa Login

**Cek 1: Apakah user account sudah dibuat?**
```sql
SELECT g.*, u.name, u.role 
FROM guru g 
LEFT JOIN users u ON g.user_id = u.id 
WHERE g.nip = '123456789';
```

Jika `user_id` NULL atau `name` NULL → User belum dibuat

**Solusi:**
- Jalankan script SQL `create_user_for_existing_guru.sql`
- Atau edit guru dan pilih user_id manual

**Cek 2: Apakah password benar?**
- Password default: `guru123`
- Cek apakah guru sudah mengubah password

**Cek 3: Apakah role benar?**
```sql
SELECT * FROM users WHERE name = 'guru_123456789';
```

Field `role` harus `'guru'` (bukan 'admin' atau 'siswa')

### Error "Token tidak valid"
- Clear browser cache
- Refresh halaman login
- Coba lagi

### Error "Kredensial tidak valid"
- Pastikan username benar: `guru_[NIP]`
- Pastikan password: `guru123`
- Cek caps lock
- Coba reset password via "Lupa Password" (jika ada)

## File yang Diubah

1. ✅ `app/controllers/GuruController.php` - Method `store()` dimodifikasi
2. ✅ `database/create_user_for_existing_guru.sql` - Script SQL baru

## File yang Perlu Di-Upload ke Server

Upload file berikut ke server production:
- `app/controllers/GuruController.php`
- `database/create_user_for_existing_guru.sql` (untuk dijalankan manual)

## Catatan Penting

1. **Backup Database**: Sebelum menjalankan script SQL, backup database terlebih dahulu
2. **Informasikan Kredensial**: Admin harus menginformasikan username & password kepada guru
3. **Ganti Password**: Guru harus mengganti password setelah login pertama kali
4. **Keamanan**: Password default `guru123` hanya untuk login pertama kali

## Rekomendasi Tambahan

### Fitur yang Bisa Ditambahkan:
1. **Force Change Password**: Paksa guru ganti password saat login pertama kali
2. **Email Notification**: Kirim email otomatis dengan kredensial login
3. **WhatsApp Notification**: Kirim WhatsApp dengan kredensial login
4. **Generate Random Password**: Password unik untuk setiap guru
5. **Bulk Import**: Import guru dari Excel dengan auto-create user

### Keamanan Tambahan:
1. **Password Complexity**: Validasi password minimal 8 karakter, ada huruf besar, angka, dll
2. **Password Expiry**: Password expired setelah 90 hari
3. **Login Attempt Limit**: Maksimal 5x salah password, akun di-lock
4. **Two-Factor Authentication**: OTP via SMS/Email

## Support

Jika ada masalah atau pertanyaan, silakan hubungi developer dengan informasi:
- Error message lengkap
- Screenshot
- Data guru yang bermasalah (NIP, nama)
