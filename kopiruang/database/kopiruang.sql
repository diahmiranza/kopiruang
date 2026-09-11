-- ============================================
-- DATABASE: kopiruang
-- Kopi Ruang Specialty Shop
-- ============================================

CREATE DATABASE IF NOT EXISTS kopiruang CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kopiruang;

-- ============================================
-- TABLE: menu_categories
-- ============================================
CREATE TABLE IF NOT EXISTS menu_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT 'coffee',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO menu_categories (name, slug, icon, sort_order) VALUES
('Espresso Based', 'espresso', 'zap', 1),
('Manual Brew', 'manual-brew', 'droplet', 2),
('Signature Drinks', 'signature', 'star', 3),
('Kue & Pastry', 'pastry', 'package', 4),
('Non Coffee', 'non-coffee', 'leaf', 5);

-- ============================================
-- TABLE: menu_items
-- ============================================
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

INSERT INTO menu_items (category_id, name, description, price, price_cold, is_bestseller) VALUES
(1, 'Americano', 'Espresso shots diluted dengan hot/cold water, rasa bold dan clean', 28000, 32000, 0),
(1, 'Latte', 'Espresso lembut dengan milk foam yang creamy dan smooth', 35000, 38000, 1),
(1, 'Cappuccino', 'Espresso, steamed milk, dan dry foam dengan rasio sempurna', 35000, 38000, 0),
(1, 'Flat White', 'Double ristretto dengan microfoam susu, lebih kuat dari latte', 38000, 42000, 0),
(1, 'Cortado', 'Espresso dengan sedikit steamed milk, intense dan balanced', 32000, NULL, 0),
(2, 'V60 Single Origin', 'Pour over dengan biji pilihan dari Aceh, Flores, atau Toraja', 45000, NULL, 1),
(2, 'Chemex', 'Seduhan bersih dan bright dengan Chemex 6-cup classic', 48000, NULL, 0),
(2, 'Aeropress', 'Versatile brew method, bisa light atau full-bodied sesuai selera', 42000, NULL, 0),
(2, 'Cold Brew', 'Steeped 18 jam, smooth dan low acid. Perfect for hot days', 45000, NULL, 1),
(3, 'Ruang Latte', 'Signature latte dengan gula aren, kayu manis, dan sea salt foam', 42000, 45000, 1),
(3, 'Kopi Susu Kental', 'Espresso dengan susu kental manis ala kedai klasik, modern twist', 30000, 33000, 1),
(3, 'Green Velvet', 'Matcha latte dengan oat milk dan vanilla bean, silky smooth', 40000, 43000, 0),
(4, 'Croissant Butter', 'Croissant renyah luar, lembut dalam, dipanggang tiap pagi', 28000, NULL, 0),
(4, 'Banana Bread', 'Banana bread moist dengan dark chocolate chips dan walnut', 25000, NULL, 1),
(4, 'Egg Tart', 'Portuguese egg tart dengan custard lembut, crust renyah', 22000, NULL, 0),
(5, 'Teh Tarik', 'Teh susu tradisional dengan teknik tarik yang menghasilkan foam natural', 25000, 28000, 0),
(5, 'Yuzu Lemonade', 'Segar, asam manis dengan citrus yuzu dan soda sparkling', 32000, NULL, 0),
(5, 'Chocolate Oat', 'Dark chocolate 70% dengan oat milk, rich dan guilt-free', 35000, 38000, 0);

-- ============================================
-- TABLE: reservations
-- ============================================
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    guests INT NOT NULL DEFAULT 2,
    notes TEXT DEFAULT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- TABLE: testimonials
-- ============================================
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    rating TINYINT NOT NULL DEFAULT 5,
    review TEXT NOT NULL,
    avatar_initial VARCHAR(5),
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO testimonials (customer_name, rating, review, avatar_initial, is_featured) VALUES
('Rizal M.', 5, 'V60-nya luar biasa. Biji Aceh Gayo yang dipakai terasa clean, fruity, dan after-taste-nya panjang. Jadi tempat wajib saya tiap minggu.', 'RM', 1),
('Dinda A.', 5, 'Ruang Latte adalah minuman terbaik yang pernah saya coba. Kombinasi gula aren dan sea salt foam-nya bikin nagih. WiFi kencang, cocok buat kerja seharian.', 'DA', 1),
('Fajar P.', 5, 'Tempat yang benar-benar menghargai kopi. Barista-nya knowledgeable, selalu mau jelasin origin dan proses biji yang digunakan. Pengalaman ngopi yang berbeda.', 'FP', 1),
('Sarah K.', 4, 'Croissant + Cold Brew-nya combo sempurna buat pagi hari. Interior minimalisnya bikin betah berlama-lama. Recommended untuk remote working!', 'SK', 1);

-- ============================================
-- TABLE: site_settings
-- ============================================
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO site_settings (setting_key, setting_value) VALUES
('cafe_name', 'Kopi Ruang'),
('cafe_tagline', 'Specialty Coffee Shop'),
('cafe_address', 'Jl. Teuku Umar No. 45, Banda Aceh'),
('cafe_phone', '+62 812-3456-7890'),
('cafe_email', 'hello@kopiruang.id'),
('cafe_instagram', 'https://instagram.com/kopiruang'),
('cafe_tiktok', 'https://tiktok.com/@kopiruang'),
('open_weekday', '07:00 - 22:00'),
('open_weekend', '08:00 - 23:00'),
('google_maps_url', 'https://maps.google.com'),
('meta_description', 'Kopi Ruang Specialty Shop - Tempat ngopi dengan biji kopi lokal berkualitas, suasana nyaman untuk kerja & hangout. Rating 4.8 bintang di Banda Aceh.');

-- ============================================
-- TABLE: admin_users
-- ============================================
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    email VARCHAR(150),
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default: username=admin, password=admin123
-- Hash di bawah adalah bcrypt dari: admin123
INSERT INTO admin_users (username, password_hash, full_name, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Kopi Ruang', 'admin@kopiruang.id');
