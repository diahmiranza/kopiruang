<?php
// $activePage should be set by each page before including this
$activePage = $activePage ?? '';
?>
<aside class="admin-sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">☕</div>
        <div class="logo-text">
            Kopi Ruang
            <small>Admin Panel</small>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Utama</div>
        <a href="dashboard.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <span class="nav-icon">📊</span> Dashboard
        </a>
        <a href="reservations.php" class="<?= $activePage === 'reservations' ? 'active' : '' ?>">
            <span class="nav-icon">📅</span> Reservasi
        </a>

        <div class="nav-section-label">Konten</div>
        <a href="menu.php" class="<?= $activePage === 'menu' ? 'active' : '' ?>">
            <span class="nav-icon">☕</span> Menu
        </a>
        <a href="testimonials.php" class="<?= $activePage === 'testimonials' ? 'active' : '' ?>">
            <span class="nav-icon">⭐</span> Ulasan
        </a>

        <div class="nav-section-label">Sistem</div>
        <a href="../index.html" target="_blank" rel="noopener">
            <span class="nav-icon">🌐</span> Lihat Website
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php">
            <span>🚪</span> Keluar
        </a>
    </div>
</aside>
