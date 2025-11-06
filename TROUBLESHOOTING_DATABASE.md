# Troubleshooting Database Connection - DomaiNesia

## Masalah
Tidak dapat terhubung ke database `wiracent_balok` di phpMyAdmin DomaiNesia.

## Perbaikan yang Telah Dilakukan

### 1. **Error Handling yang Lebih Baik**
   - ✅ Error messages sekarang lebih detail ketika `APP_DEBUG=true`
   - ✅ Menampilkan informasi konfigurasi (host, database, user) saat error
   - ✅ Error logging lebih lengkap untuk debugging

### 2. **Konfigurasi Timeout**
   - ✅ Menambahkan timeout untuk mencegah hanging connection
   - ✅ Kompatibel dengan berbagai versi PHP

### 3. **Pesan Error di Login Page**
   - ✅ Sekarang menampilkan pesan database error dengan jelas
   - ✅ Membedakan antara network error dan database connection error

### 4. **Script Testing**
   - ✅ File `public/test-db-connection.php` untuk menguji koneksi database

## Langkah Troubleshooting

### Step 1: Test Koneksi Database
1. Buka browser dan akses:
   ```
   https://bcs.wiracenter.com/test-db-connection.php
   ```
2. Script ini akan menampilkan:
   - Konfigurasi database yang digunakan
   - Status koneksi
   - Pesan error detail (jika ada)
   - Saran perbaikan

### Step 2: Verifikasi Konfigurasi di `config.env`

Pastikan konfigurasi di file `config.env` sesuai:

```env
DB_HOST=localhost
DB_NAME=wiracent_balok
DB_USER=wiracent_balok
DB_PASS=balok2025!
APP_DEBUG=true
```

**Penting untuk DomaiNesia:**
- `DB_HOST` biasanya adalah `localhost` untuk shared hosting
- Jika `localhost` tidak bekerja, coba:
  - Nama domain Anda (misalnya: `mysql.yourdomain.com`)
  - Atau cek di cPanel > MySQL Databases untuk melihat MySQL hostname

### Step 3: Verifikasi Database di phpMyAdmin

1. Login ke **cPanel DomaiNesia**
2. Buka **phpMyAdmin**
3. Pastikan:
   - ✅ Database `wiracent_balok` ada
   - ✅ User database `wiracent_balok` ada
   - ✅ User memiliki akses penuh ke database tersebut
   - ✅ Password sesuai dengan yang di `config.env`

### Step 4: Cek Error di Browser Console

1. Buka halaman login
2. Tekan **F12** untuk membuka Developer Tools
3. Pilih tab **Console**
4. Coba login lagi
5. Periksa error messages yang muncul

### Step 5: Cek Error di Server Logs

Jika memiliki akses ke error logs, cek file log untuk melihat detail error:
- Lokasi log biasanya di cPanel > Error Logs
- Atau file `error_log` di direktori web root

## Masalah Umum dan Solusi

### Error: "Access denied for user"
**Solusi:**
- Verifikasi `DB_USER` dan `DB_PASS` di `config.env`
- Pastikan user database memiliki privilege yang benar
- Coba reset password database di cPanel

### Error: "Unknown database 'wiracent_balok'"
**Solusi:**
- Pastikan nama database di `config.env` tepat (case-sensitive)
- Buat database jika belum ada di cPanel > MySQL Databases
- Import database.sql jika belum ada tabel

### Error: "Connection refused" atau "Can't connect to MySQL server"
**Solusi:**
1. Pastikan MySQL service berjalan di server
2. Coba ubah `DB_HOST` dari `localhost` ke:
   - Domain Anda: `mysql.yourdomain.com`
   - Atau cek MySQL hostname di cPanel
3. Untuk remote connection, pastikan "Remote MySQL" diaktifkan di cPanel

### Error: "Connection timeout"
**Solusi:**
- Cek firewall settings
- Pastikan port MySQL (3306) tidak diblokir
- Cek apakah server overload

## Verifikasi Database Credentials di cPanel

1. Login ke **cPanel DomaiNesia**
2. Klik **MySQL Databases**
3. Di bagian **Current Databases**, cek:
   - Nama database: `wiracent_balok`
   - Username: `wiracent_balok`
   - Hostname: biasanya `localhost` atau hostname khusus
4. Klik **phpMyAdmin** di cPanel
5. Login dengan credentials database
6. Pastikan database dan tabel ada

## Testing Manual Koneksi

Jika ingin test manual via PHP:

```php
<?php
$host = 'localhost';
$dbname = 'wiracent_balok';
$user = 'wiracent_balok';
$pass = 'balok2025!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    echo "Connection successful!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
```

## Checklist

- [ ] `config.env` sudah dikonfigurasi dengan benar
- [ ] Database `wiracent_balok` ada di phpMyAdmin
- [ ] User database `wiracent_balok` ada dan memiliki akses
- [ ] Password database sesuai dengan `config.env`
- [ ] Sudah test koneksi via `test-db-connection.php`
- [ ] Sudah cek error logs
- [ ] `APP_DEBUG=true` untuk melihat error detail
- [ ] File `test-db-connection.php` sudah dihapus setelah testing (untuk keamanan)

## Catatan Keamanan

⚠️ **PENTING:** Setelah selesai troubleshooting:
1. Set `APP_DEBUG=false` di `config.env` untuk production
2. **HAPUS** file `public/test-db-connection.php` untuk keamanan
3. Pastikan file `config.env` tidak dapat diakses dari web browser

## Bantuan Tambahan

Jika masih mengalami masalah:
1. Hubungi support DomaiNesia untuk verifikasi:
   - MySQL server status
   - Database dan user permissions
   - MySQL hostname yang benar
2. Berikan informasi error dari:
   - `test-db-connection.php`
   - Browser console (F12)
   - Server error logs

