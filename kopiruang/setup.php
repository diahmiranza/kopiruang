<?php
// ================================================
// Kopi Ruang — AUTO INSTALLER
// Buka: http://localhost/kopiruang/setup.php
// ================================================

error_reporting(E_ALL);
ini_set('display_errors', 1);

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'kopiruang';

$steps = [];
$hasError = false;

// ---- STEP 1: Cek PDO ----
if (!extension_loaded('pdo_mysql')) {
    $steps[] = ['fail', 'PDO MySQL', 'Ekstensi pdo_mysql tidak aktif. Buka php.ini dan aktifkan extension=pdo_mysql'];
    $hasError = true;
} else {
    $steps[] = ['ok', 'PDO MySQL', 'Ekstensi aktif ✓'];
}

// ---- STEP 2: Konek MySQL ----
$pdo = null;
if (!$hasError) {
    try {
        $pdo = new PDO("mysql:host=$DB_HOST;charset=utf8mb4", $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $steps[] = ['ok', 'Koneksi MySQL', "Terhubung sebagai <b>$DB_USER@$DB_HOST</b> ✓"];
    } catch (PDOException $e) {
        $steps[] = ['fail', 'Koneksi MySQL', 'GAGAL: ' . $e->getMessage() . '<br><br>Pastikan MySQL di XAMPP sudah <b>ON</b> (hijau).'];
        $hasError = true;
    }
}

// ---- STEP 3: Buat Database ----
if (!$hasError && $pdo) {
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$DB_NAME`");
        $steps[] = ['ok', 'Database', "Database <b>$DB_NAME</b> siap ✓"];
    } catch (PDOException $e) {
        $steps[] = ['fail', 'Database', 'Gagal buat database: ' . $e->getMessage()];
        $hasError = true;
    }
}

// ---- STEP 4: Buat semua tabel ----
if (!$hasError && $pdo) {
    $sql = "
    CREATE TABLE IF NOT EXISTS menu_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE,
        icon VARCHAR(50) DEFAULT 'coffee',
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS menu_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT NOT NULL,
        name VARCHAR(150) NOT NULL,
        description TEXT,
        price INT NOT NULL,
        price_cold INT DEFAULT NULL,
        image_url VARCHAR(255) DEFAULT NULL,
        is_bestseller TINYINT(1) DEFAULT 0,
        is_available TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES menu_categories(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS reservations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        email VARCHAR(150),
        phone VARCHAR(20) NOT NULL,
        date DATE NOT NULL,
        time TIME NOT NULL,
        guests INT NOT NULL DEFAULT 2,
        notes TEXT DEFAULT NULL,
        status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS testimonials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(100) NOT NULL,
        rating TINYINT NOT NULL DEFAULT 5,
        review TEXT NOT NULL,
        avatar_initial VARCHAR(5),
        is_featured TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS site_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(100),
        email VARCHAR(150),
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;
    ";

    try {
        // Jalankan satu per satu
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $q) {
            if ($q) $pdo->exec($q);
        }
        $steps[] = ['ok', 'Buat Tabel', 'Semua tabel berhasil dibuat ✓'];
    } catch (PDOException $e) {
        $steps[] = ['fail', 'Buat Tabel', 'Error: ' . $e->getMessage()];
        $hasError = true;
    }
}

// ---- STEP 5: Isi data awal ----
if (!$hasError && $pdo) {
    try {
        // Kategori
        $pdo->exec("INSERT IGNORE INTO menu_categories (name, slug, sort_order) VALUES
            ('Espresso Based','espresso',1),
            ('Manual Brew','manual-brew',2),
            ('Signature Drinks','signature',3),
            ('Kue & Pastry','pastry',4),
            ('Non Coffee','non-coffee',5)");

        // Menu items
        $pdo->exec("INSERT IGNORE INTO menu_items (category_id,name,description,price,price_cold,is_bestseller) VALUES
            (1,'Americano','Espresso shots dengan hot/cold water, rasa bold dan clean',28000,32000,0),
            (1,'Latte','Espresso lembut dengan milk foam yang creamy',35000,38000,1),
            (1,'Cappuccino','Espresso, steamed milk, dan dry foam',35000,38000,0),
            (1,'Flat White','Double ristretto dengan microfoam susu',38000,42000,0),
            (2,'V60 Single Origin','Pour over biji pilihan dari Aceh/Flores/Toraja',45000,NULL,1),
            (2,'Cold Brew','Steeped 18 jam, smooth dan low acid',45000,NULL,1),
            (3,'Ruang Latte','Latte dengan gula aren dan sea salt foam',42000,45000,1),
            (3,'Kopi Susu Kental','Espresso dengan susu kental manis',30000,33000,1),
            (4,'Croissant Butter','Dipanggang segar tiap pagi',28000,NULL,0),
            (4,'Banana Bread','Moist dengan dark chocolate chips',25000,NULL,1),
            (5,'Teh Tarik','Teh susu dengan teknik tarik natural',25000,28000,0),
            (5,'Yuzu Lemonade','Segar asam manis dengan citrus yuzu',32000,NULL,0)");

        // Testimonials
        $pdo->exec("INSERT IGNORE INTO testimonials (customer_name,rating,review,avatar_initial,is_featured) VALUES
            ('Rizal M.',5,'V60-nya luar biasa. Biji Aceh Gayo terasa clean dan fruity. Jadi tempat wajib tiap minggu.','RM',1),
            ('Dinda A.',5,'Ruang Latte terbaik! Kombinasi gula aren dan sea salt foam bikin nagih. WiFi kencang buat kerja.','DA',1),
            ('Fajar P.',5,'Barista knowledgeable, selalu jelasin origin biji yang dipakai. Pengalaman ngopi berbeda.','FP',1),
            ('Sarah K.',4,'Croissant + Cold Brew combo sempurna. Interior minimalis bikin betah berlama-lama.','SK',1)");

        // Settings
        $pdo->exec("INSERT IGNORE INTO site_settings (setting_key,setting_value) VALUES
            ('cafe_name','Kopi Ruang'),
            ('cafe_address','Jl. Teuku Umar No. 45, Banda Aceh'),
            ('cafe_phone','+62 812-3456-7890'),
            ('cafe_email','hello@kopiruang.id'),
            ('open_weekday','07:00 - 22:00'),
            ('open_weekend','08:00 - 23:00')");

        $steps[] = ['ok', 'Data Awal', 'Menu, testimoni, dan settings berhasil diisi ✓'];
    } catch (PDOException $e) {
        $steps[] = ['warn', 'Data Awal', 'Peringatan (tidak fatal): ' . $e->getMessage()];
    }
}

// ---- STEP 6: Buat / reset admin ----
if (!$hasError && $pdo) {
    try {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        // Hapus admin lama kalau ada, insert ulang
        $pdo->exec("DELETE FROM admin_users WHERE username = 'admin'");
        $stmt = $pdo->prepare("INSERT INTO admin_users (username,password_hash,full_name,email) VALUES ('admin',:h,'Admin Kopi Ruang','admin@kopiruang.id')");
        $stmt->execute([':h' => $hash]);
        $steps[] = ['ok', 'Akun Admin', 'Akun admin berhasil dibuat/direset. Password: <b>admin123</b> ✓'];
    } catch (PDOException $e) {
        $steps[] = ['fail', 'Akun Admin', 'Gagal: ' . $e->getMessage()];
        $hasError = true;
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup – Kopi Ruang</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:system-ui,sans-serif;background:#0f2419;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem}
        .card{background:#fff;border-radius:20px;padding:2.5rem;max-width:560px;width:100%;box-shadow:0 24px 80px rgba(0,0,0,.5)}
        h1{font-size:1.5rem;color:#0f2419;margin-bottom:.2rem}
        .sub{color:#999;font-size:.82rem;margin-bottom:2rem}
        .step{display:flex;gap:.85rem;align-items:flex-start;padding:1rem;border-radius:10px;margin-bottom:.7rem;font-size:.85rem;border:1px solid}
        .ok  {background:#f0faf5;border-color:#a8dfc0;color:#1a5c36}
        .fail{background:#fff0f0;border-color:#f0b0b0;color:#8b1a1a}
        .warn{background:#fffbf0;border-color:#f0dfa0;color:#7a5a00}
        .icon{font-size:1.2rem;flex-shrink:0}
        .label{font-weight:700;margin-bottom:.2rem}
        .desc{line-height:1.55;opacity:.9}
        code{background:#f0ede8;padding:.1rem .35rem;border-radius:4px;font-size:.8rem;font-family:monospace}
        .actions{display:flex;gap:.75rem;margin-top:1.75rem;flex-wrap:wrap}
        .btn{display:inline-flex;align-items:center;gap:.4rem;padding:.75rem 1.4rem;border-radius:10px;font-size:.88rem;font-weight:700;text-decoration:none;transition:.2s}
        .btn-green{background:#1a3a2e;color:#fff}
        .btn-green:hover{background:#24503f}
        .btn-gold{background:#c9a96e;color:#0f2419}
        .btn-gold:hover{background:#e2c99a}
        .btn-gray{background:#f0ede8;color:#555}
        .success-box{background:#f0faf5;border:2px solid #a8dfc0;border-radius:12px;padding:1.5rem;margin-top:1.5rem;text-align:center}
        .success-box h2{color:#1a5c36;font-size:1.2rem;margin-bottom:.5rem}
        .success-box p{color:#3a7a56;font-size:.85rem;line-height:1.6}
        .cred{display:inline-block;background:#1a3a2e;color:#c9a96e;padding:.4rem 1rem;border-radius:8px;font-family:monospace;font-size:.9rem;margin:.3rem .2rem}
    </style>
</head>
<body>
<div class="card">
    <h1>☕ Kopi Ruang — Auto Installer</h1>
    <p class="sub">Instalasi otomatis database dan akun admin</p>

    <?php foreach ($steps as [$type, $label, $desc]): ?>
    <div class="step <?= $type ?>">
        <div class="icon"><?= $type === 'ok' ? '✅' : ($type === 'fail' ? '❌' : '⚠️') ?></div>
        <div>
            <div class="label"><?= $label ?></div>
            <div class="desc"><?= $desc ?></div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (!$hasError): ?>
    <div class="success-box">
        <h2>🎉 Instalasi Berhasil!</h2>
        <p>Login admin dengan kredensial berikut:</p>
        <div style="margin:.75rem 0">
            <span class="cred">admin</span>
            <span style="color:#999;font-size:.85rem">password →</span>
            <span class="cred">admin123</span>
        </div>
        <p style="color:#888;font-size:.78rem;margin-top:.5rem">Hapus file <code>setup.php</code> setelah login pertama.</p>
    </div>
    <div class="actions">
        <a href="admin/login.php" class="btn btn-green">🔐 Buka Admin Panel</a>
        <a href="index.html" class="btn btn-gold">🌐 Lihat Website</a>
    </div>
    <?php else: ?>
    <div class="actions">
        <a href="javascript:location.reload()" class="btn btn-gray">🔄 Coba Lagi</a>
    </div>
    <p style="margin-top:1rem;font-size:.78rem;color:#aaa">
        Pastikan <b>MySQL di XAMPP sudah ON</b> lalu refresh halaman ini.
    </p>
    <?php endif; ?>
</div>
</body>
</html>
