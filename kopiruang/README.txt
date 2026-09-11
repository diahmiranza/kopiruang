================================================
  KOPI RUANG — Specialty Coffee Shop Website
  Full Package (HTML + CSS + PHP + MySQL)
================================================

STRUKTUR FILE
=============
kopiruang/
├── index.html              ← Halaman utama website
├── config.php              ← Konfigurasi database
├── css/
│   ├── style.css           ← CSS halaman utama
│   └── admin.css           ← CSS admin panel
├── js/
│   └── main.js             ← JavaScript halaman utama
├── api/
│   └── reservation.php     ← API simpan reservasi
├── admin/
│   ├── login.php           ← Login admin
│   ├── dashboard.php       ← Dashboard admin
│   ├── reservations.php    ← Kelola reservasi
│   ├── menu.php            ← Kelola menu
│   ├── testimonials.php    ← Kelola ulasan
│   ├── sidebar.php         ← Komponen sidebar
│   ├── auth.php            ← Guard login
│   └── logout.php          ← Logout
├── database/
│   └── kopiruang.sql       ← File database (import ke phpMyAdmin)
└── images/
    └── logo.png            ← Logo Kopi Ruang

================================================
CARA INSTALASI (XAMPP/WAMP/LOCALHOST)
================================================

LANGKAH 1 — COPY FILE
-----------------------
Salin folder "kopiruang" ke:
  C:\xampp\htdocs\kopiruang\   (Windows XAMPP)
  /var/www/html/kopiruang/     (Linux/Mac)

LANGKAH 2 — IMPORT DATABASE
------------------------------
1. Buka phpMyAdmin di browser: http://localhost/phpmyadmin
2. Klik tab "Import"
3. Pilih file: database/kopiruang.sql
4. Klik "Go" / "Import"

LANGKAH 3 — KONFIGURASI (jika perlu)
---------------------------------------
Buka file config.php, sesuaikan jika berbeda:
  define('DB_HOST', 'localhost');
  define('DB_USER', 'root');
  define('DB_PASS', '');           ← isi jika punya password MySQL
  define('DB_NAME', 'kopiruang');

LANGKAH 4 — BUKA WEBSITE
---------------------------
Website utama : http://localhost/kopiruang/
Admin panel   : http://localhost/kopiruang/admin/login.php

================================================
LOGIN ADMIN
================================================
Username : admin
Password : KopiRuang2024

(Ganti password setelah login pertama via phpMyAdmin
 di tabel admin_users, kolom password_hash,
 generate hash baru dengan: password_hash('passwordbaru', PASSWORD_DEFAULT) )

================================================
FITUR WEBSITE
================================================
✓ Halaman utama: Hero, Tentang, Menu, Suasana, Ulasan, Reservasi, Footer
✓ SEO ready: meta tags, Open Graph, Schema.org JSON-LD, semantic HTML
✓ Responsive: mobile, tablet, desktop
✓ Animasi: scroll reveal, counter, floating logo
✓ Form reservasi dengan penyimpanan ke database
✓ Tombol WhatsApp floating
✓ Accessible: ARIA labels, semantic HTML5

FITUR ADMIN
================================================
✓ Dashboard dengan statistik ringkasan
✓ Kelola Reservasi: lihat, konfirmasi, batalkan, hapus
✓ Kelola Menu: tambah, edit, hapus, toggle aktif/nonaktif
✓ Kelola Ulasan: tambah, hapus, toggle tampil/sembunyikan
✓ Session-based authentication yang aman

================================================
CATATAN PENTING
================================================
- Pastikan ekstensi PHP: PDO, pdo_mysql, mbstring aktif
- PHP versi minimum: 7.4+
- MySQL/MariaDB versi minimum: 5.7+
- Untuk produksi: ubah DB_PASS dan ganti password admin
- HTTPS sangat disarankan untuk deployment publik

================================================
KONTAK & CUSTOMISASI
================================================
Untuk customisasi lebih lanjut (domain, konten, warna, dll),
semua bisa diubah di file-file berikut:
- index.html    → konten website utama
- config.php    → setting database & URL
- css/style.css → warna, font, layout utama
================================================
