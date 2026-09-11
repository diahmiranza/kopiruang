<?php
require_once 'auth.php';
require_once '../config.php';
$activePage = 'dashboard';

// Semua query dibungkus try-catch agar tidak crash
$totalMenu = $totalReserv = $pendingReserv = $totalReviews = 0;
$recentReserv = [];
$dbError = null;

try {
    $pdo = getDB();
} catch (Exception $e) {
    $dbError = $e->getMessage();
    $pdo = null;
}

if ($pdo) {
    // Buat tabel yang kurang pakai MyISAM (tidak ada foreign key constraint)
    $createTables = [
        "CREATE TABLE IF NOT EXISTS menu_categories (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, sort_order INT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS menu_items (id INT AUTO_INCREMENT PRIMARY KEY, category_id INT NOT NULL, name VARCHAR(150) NOT NULL, description TEXT, price INT NOT NULL, price_cold INT DEFAULT NULL, is_bestseller TINYINT(1) DEFAULT 0, is_available TINYINT(1) DEFAULT 1, sort_order INT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS reservations (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, email VARCHAR(150), phone VARCHAR(20) NOT NULL, date DATE NOT NULL, time TIME NOT NULL, guests INT NOT NULL DEFAULT 2, notes TEXT, status VARCHAR(20) DEFAULT 'pending', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS testimonials (id INT AUTO_INCREMENT PRIMARY KEY, customer_name VARCHAR(100) NOT NULL, rating TINYINT NOT NULL DEFAULT 5, review TEXT NOT NULL, avatar_initial VARCHAR(5), is_featured TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS admin_users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) NOT NULL, password_hash VARCHAR(255) NOT NULL, full_name VARCHAR(100), email VARCHAR(150), last_login TIMESTAMP NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4",
    ];

    foreach ($createTables as $sql) {
        try { $pdo->exec($sql); } catch (Exception $e) {}
    }

    // Pastikan admin ada
    try {
        $adm = $pdo->query("SELECT COUNT(*) FROM admin_users WHERE username='admin'")->fetchColumn();
        if ($adm == 0) {
            $h = password_hash('admin123', PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO admin_users (username,password_hash,full_name,email) VALUES ('admin',?,'Admin Kopi Ruang','admin@kopiruang.id')")
                ->execute([$h]);
        }
    } catch (Exception $e) {}

    // Ambil statistik
    try { $totalMenu     = $pdo->query("SELECT COUNT(*) FROM menu_items WHERE is_available=1")->fetchColumn(); } catch(Exception $e){}
    try { $totalReserv   = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn(); } catch(Exception $e){}
    try { $pendingReserv = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status='pending'")->fetchColumn(); } catch(Exception $e){}
    try { $totalReviews  = $pdo->query("SELECT COUNT(*) FROM testimonials")->fetchColumn(); } catch(Exception $e){}
    try { $recentReserv  = $pdo->query("SELECT * FROM reservations ORDER BY created_at DESC LIMIT 6")->fetchAll(); } catch(Exception $e){}
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard – Kopi Ruang Admin</title>
<meta name="robots" content="noindex">
<link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>
<main class="admin-main">
    <div class="admin-topbar">
        <div>
            <div class="topbar-title">Dashboard</div>
            <div class="topbar-subtitle">Selamat datang, <?= htmlspecialchars($adminName) ?>!</div>
        </div>
        <div class="topbar-right">
            <a href="../index.html" class="btn btn-outline btn-sm" target="_blank">🌐 Website</a>
            <div class="topbar-user">
                <div class="user-avatar"><?= strtoupper(substr($adminUser,0,2)) ?></div>
                <span><?= htmlspecialchars($adminUser) ?></span>
            </div>
        </div>
    </div>

    <div class="admin-content">
        <?php if ($dbError): ?>
        <div class="alert alert-danger">⚠ <?= htmlspecialchars($dbError) ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-icon green">☕</div>
                <div class="stat-card-info">
                    <div class="stat-card-number"><?= $totalMenu ?></div>
                    <div class="stat-card-label">Menu Aktif</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon gold">📅</div>
                <div class="stat-card-info">
                    <div class="stat-card-number"><?= $totalReserv ?></div>
                    <div class="stat-card-label">Total Reservasi</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon orange">⏳</div>
                <div class="stat-card-info">
                    <div class="stat-card-number"><?= $pendingReserv ?></div>
                    <div class="stat-card-label">Pending</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon blue">⭐</div>
                <div class="stat-card-info">
                    <div class="stat-card-number"><?= $totalReviews ?></div>
                    <div class="stat-card-label">Ulasan</div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">📋 Reservasi Terbaru</span>
                <a href="reservations.php" class="btn btn-outline btn-sm">Lihat Semua</a>
            </div>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr><th>#</th><th>Nama</th><th>Tanggal</th><th>Tamu</th><th>WhatsApp</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($recentReserv)): ?>
                        <tr><td colspan="7" style="text-align:center;color:#aaa;padding:2rem;">Belum ada reservasi masuk.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentReserv as $r): ?>
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td><strong><?= htmlspecialchars($r['name']) ?></strong></td>
                            <td><?= date('d M Y', strtotime($r['date'])) ?> <?= substr($r['time'],0,5) ?></td>
                            <td><?= $r['guests'] ?> org</td>
                            <td><a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$r['phone']) ?>" target="_blank" style="color:var(--green-dark);font-size:.8rem;"><?= htmlspecialchars($r['phone']) ?></a></td>
                            <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                            <td>
                                <a href="reservations.php?action=confirm&id=<?= $r['id'] ?>" class="btn btn-sm" style="background:rgba(58,170,106,0.1);color:#1e7048;">✓</a>
                                <a href="reservations.php?action=cancel&id=<?= $r['id'] ?>"  class="btn btn-sm btn-danger">✗</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header"><span class="panel-title">⚡ Akses Cepat</span></div>
            <div class="panel-body" style="display:flex;gap:1rem;flex-wrap:wrap;">
                <a href="menu.php" class="btn btn-primary-admin">☕ Kelola Menu</a>
                <a href="reservations.php" class="btn btn-gold">📅 Kelola Reservasi</a>
                <a href="testimonials.php" class="btn btn-outline">⭐ Kelola Ulasan</a>
                <a href="../index.html" class="btn btn-outline" target="_blank">🌐 Buka Website</a>
            </div>
        </div>
    </div>
</main>
</body>
</html>
