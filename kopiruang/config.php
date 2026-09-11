<?php
define('DB_HOST',    'localhost');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_NAME',    'kopiruang');
define('DB_CHARSET', 'utf8mb4');

define('SITE_URL',  'http://localhost/kopiruang');
define('SITE_NAME', 'Kopi Ruang');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        // Auto-create semua tabel yang dibutuhkan
        $pdo->exec("
        CREATE TABLE IF NOT EXISTS menu_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            icon VARCHAR(50) DEFAULT 'coffee',
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS testimonials (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_name VARCHAR(100) NOT NULL,
            rating TINYINT NOT NULL DEFAULT 5,
            review TEXT NOT NULL,
            avatar_initial VARCHAR(5),
            is_featured TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS site_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(100),
            email VARCHAR(150),
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Seed data awal kalau kosong
        $catCount = $pdo->query("SELECT COUNT(*) FROM menu_categories")->fetchColumn();
        if ($catCount == 0) {
            $pdo->exec("INSERT IGNORE INTO menu_categories (name,slug,sort_order) VALUES
                ('Espresso Based','espresso',1),
                ('Manual Brew','manual-brew',2),
                ('Signature Drinks','signature',3),
                ('Kue & Pastry','pastry',4),
                ('Non Coffee','non-coffee',5)");

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

            $pdo->exec("INSERT IGNORE INTO testimonials (customer_name,rating,review,avatar_initial,is_featured) VALUES
                ('Rizal M.',5,'V60-nya luar biasa. Biji Aceh Gayo terasa clean dan fruity.','RM',1),
                ('Dinda A.',5,'Ruang Latte terbaik! Bikin nagih. WiFi kencang buat kerja.','DA',1),
                ('Fajar P.',5,'Barista knowledgeable, selalu jelasin origin biji.','FP',1),
                ('Sarah K.',4,'Croissant + Cold Brew combo sempurna buat pagi hari.','SK',1)");
        }

        // Pastikan admin selalu ada
        $adminExists = $pdo->query("SELECT COUNT(*) FROM admin_users WHERE username='admin'")->fetchColumn();
        if ($adminExists == 0) {
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO admin_users (username,password_hash,full_name,email) VALUES ('admin',:h,'Admin Kopi Ruang','admin@kopiruang.id')")
                ->execute([':h' => $hash]);
        }
    }
    return $pdo;
}

function clean($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function formatRupiah($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}
?>
