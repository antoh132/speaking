# SpeakOn! — Panduan Setup XAMPP

Panduan lengkap untuk menjalankan SpeakOn! di lingkungan lokal menggunakan XAMPP.

---

## Prasyarat

- [XAMPP](https://www.apachefriends.org/) versi 8.x (PHP 8.x + MySQL/MariaDB)
- [Composer](https://getcomposer.org/) (PHP package manager)
- Browser modern (Chrome, Firefox, Edge)

---

## Langkah 1 — Install XAMPP

1. Download XAMPP dari https://www.apachefriends.org/
2. Install dan jalankan **XAMPP Control Panel**
3. Start **Apache** dan **MySQL**

---

## Langkah 2 — Copy Folder ke htdocs

1. Copy seluruh folder `speakon/` ke:
   ```
   C:\xampp\htdocs\speakon\
   ```
2. Pastikan struktur folder seperti ini:
   ```
   C:\xampp\htdocs\speakon\
   ├── api/
   ├── database/
   ├── frontend/
   ├── uploads/
   ├── logs/
   ├── index.html
   ├── login.html
   ├── dashboard-superadmin.html
   ├── dashboard-dosen.html
   ├── dashboard-siswa.html
   └── ...
   ```

---

## Langkah 3 — Install Composer Dependencies

Buka terminal/command prompt di folder `speakon/`:

```bash
cd C:\xampp\htdocs\speakon
composer require firebase/php-jwt
```

Ini akan membuat folder `vendor/` dengan library JWT.

---

## Langkah 4 — Import Database

1. Buka browser, akses: `http://localhost/phpmyadmin`
2. Klik **New** di sidebar kiri
3. Buat database baru bernama: `speakon_db`
4. Pilih database `speakon_db`, klik tab **Import**
5. Pilih file: `database/schema.sql`
6. Klik **Go** / **Import**

Database akan membuat semua tabel dan data awal (termasuk akun superadmin default).

**Akun default superadmin:**
- Email: `admin@speakon.id`
- Password: `Admin@123`

> ⚠️ **Ganti password superadmin segera setelah login pertama!**

---

## Langkah 5 — Konfigurasi Database

Edit file `api/config/config.php` sesuai konfigurasi XAMPP Anda:

```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'speakon_db');
define('DB_USER', 'root');      // Username MySQL XAMPP default
define('DB_PASS', '');          // Password MySQL XAMPP default (kosong)
```

Jika MySQL XAMPP Anda menggunakan password, isi `DB_PASS` dengan password tersebut.

---

## Langkah 6 — Konfigurasi JWT Secret

Di file `api/config/config.php`, ganti JWT secret dengan string acak yang kuat:

```php
define('JWT_SECRET',         'ganti_dengan_string_acak_panjang_minimal_32_karakter');
define('JWT_REFRESH_SECRET', 'ganti_dengan_string_acak_berbeda_minimal_32_karakter');
```

Gunakan generator seperti: https://generate-secret.vercel.app/32

---

## Langkah 7 — Konfigurasi PHP untuk Upload File

Edit file `C:\xampp\php\php.ini`:

Cari dan ubah nilai berikut:
```ini
upload_max_filesize = 50M
post_max_size = 55M
max_execution_time = 120
```

Setelah mengubah `php.ini`, **restart Apache** di XAMPP Control Panel.

---

## Langkah 8 — Aktifkan mod_rewrite Apache

1. Buka `C:\xampp\apache\conf\httpd.conf`
2. Cari baris: `#LoadModule rewrite_module modules/mod_rewrite.so`
3. Hapus tanda `#` di depannya
4. Cari bagian `<Directory "C:/xampp/htdocs">` dan pastikan:
   ```apache
   AllowOverride All
   ```
5. **Restart Apache**

---

## Langkah 9 — Akses Aplikasi

Buka browser dan akses:

```
http://localhost/speakon/login.html
```

Login dengan akun superadmin default:
- Email: `admin@speakon.id`
- Password: `Admin@123`

---

## Struktur URL API

Semua API endpoint dapat diakses di:

```
http://localhost/speakon/api/{resource}
```

Contoh:
- `POST http://localhost/speakon/api/auth/login`
- `GET  http://localhost/speakon/api/users`
- `GET  http://localhost/speakon/api/levels/progress`

---

## Troubleshooting

### Error: "DB_UNAVAILABLE"
- Pastikan MySQL sudah running di XAMPP Control Panel
- Periksa konfigurasi `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` di `config.php`

### Error: 404 pada endpoint API
- Pastikan `mod_rewrite` sudah aktif di Apache
- Pastikan `AllowOverride All` sudah diset di `httpd.conf`
- Restart Apache setelah perubahan

### Error: Upload file gagal
- Periksa `upload_max_filesize` dan `post_max_size` di `php.ini`
- Pastikan folder `uploads/recordings/` dapat ditulis (writable)
- Restart Apache setelah mengubah `php.ini`

### Error: "vendor/autoload.php not found"
- Jalankan `composer require firebase/php-jwt` di folder `speakon/`
- Pastikan Composer sudah terinstall

### Logs
- Error database: `logs/db-errors.log`
- Error audit: `logs/audit-errors.log`
- Error auth: `logs/auth-errors.log`

---

## Keamanan (Sebelum Production)

- [ ] Ganti JWT_SECRET dan JWT_REFRESH_SECRET dengan nilai acak yang kuat
- [ ] Ganti password superadmin default
- [ ] Set `APP_ENV = 'production'` di `config.php`
- [ ] Batasi akses ke folder `logs/` dan `database/` via `.htaccess`
- [ ] Aktifkan HTTPS (SSL) di Apache
- [ ] Buat MySQL user khusus dengan hak akses terbatas (bukan `root`)

---

## Kontak & Support

Untuk pertanyaan teknis, hubungi tim pengembang SpeakOn!.
