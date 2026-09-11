<?php
require_once 'auth.php';
require_once '../config.php';

$pdo = getDB();
$activePage = 'reservations';
$msg = '';

// Handle status update
if (isset($_GET['action'], $_GET['id'])) {
    $id     = (int)$_GET['id'];
    $action = $_GET['action'];
    if (in_array($action, ['confirm', 'cancel', 'delete'])) {
        try {
            if ($action === 'delete') {
                $pdo->prepare("DELETE FROM reservations WHERE id = :id")->execute([':id' => $id]);
                $msg = 'success:Reservasi berhasil dihapus.';
            } else {
                $status = $action === 'confirm' ? 'confirmed' : 'cancelled';
                $pdo->prepare("UPDATE reservations SET status = :s WHERE id = :id")
                    ->execute([':s' => $status, ':id' => $id]);
                $msg = 'success:Status reservasi diperbarui.';
            }
        } catch (PDOException $e) {
            $msg = 'error:Gagal memperbarui.';
        }
        header('Location: reservations.php?msg=' . urlencode($msg));
        exit;
    }
}

if (!empty($_GET['msg'])) {
    [$msgType, $msgText] = explode(':', $_GET['msg'], 2);
}

// Filter
$filter = $_GET['status'] ?? 'all';
$where  = $filter !== 'all' ? "WHERE status = " . $pdo->quote($filter) : "";

$reservations = $pdo->query("SELECT * FROM reservations $where ORDER BY date DESC, time DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi – Kopi Ruang Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%231a3a2e' width='100' height='100' rx='20'/><text y='.9em' font-size='70' x='10'>☕</text></svg>">
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="admin-main">
    <div class="admin-topbar">
        <div>
            <div class="topbar-title">Kelola Reservasi</div>
            <div class="topbar-subtitle">Total: <?= count($reservations) ?> reservasi <?= $filter !== 'all' ? "($filter)" : '' ?></div>
        </div>
        <div class="topbar-right">
            <div class="topbar-user">
                <div class="user-avatar"><?= strtoupper(substr($adminUser, 0, 2)) ?></div>
                <span><?= htmlspecialchars($adminUser) ?></span>
            </div>
        </div>
    </div>

    <div class="admin-content">
        <?php if (!empty($msgType)): ?>
        <div class="alert alert-<?= $msgType === 'success' ? 'success' : 'danger' ?>">
            <?= $msgType === 'success' ? '✓' : '⚠' ?> <?= htmlspecialchars($msgText) ?>
        </div>
        <?php endif; ?>

        <!-- Filter Bar -->
        <div class="panel" style="margin-bottom:1rem;">
            <div class="panel-body" style="padding:1rem 1.5rem; display:flex; gap:.5rem; align-items:center; flex-wrap:wrap;">
                <span style="font-size:.8rem; color:var(--text-light); margin-right:.5rem;">Filter:</span>
                <?php foreach (['all' => 'Semua', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled'] as $val => $label): ?>
                <a href="?status=<?= $val ?>" class="btn btn-sm <?= $filter === $val ? 'btn-primary-admin' : 'btn-outline' ?>">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">📋 Daftar Reservasi</span>
            </div>
            <div class="table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Nama</th>
                            <th>Tanggal & Jam</th>
                            <th>Tamu</th>
                            <th>Kontak</th>
                            <th>Catatan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($reservations)): ?>
                        <tr><td colspan="8" style="text-align:center;color:#aaa;padding:2rem;">Belum ada data reservasi.</td></tr>
                    <?php else: ?>
                        <?php foreach ($reservations as $r): ?>
                        <tr>
                            <td style="color:var(--text-light);">#<?= $r['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($r['name']) ?></strong><br>
                                <small style="color:#aaa;"><?= htmlspecialchars($r['email']) ?></small>
                            </td>
                            <td>
                                <?= date('d M Y', strtotime($r['date'])) ?><br>
                                <small style="color:#aaa;"><?= substr($r['time'], 0, 5) ?> WIB</small>
                            </td>
                            <td><?= $r['guests'] ?></td>
                            <td>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $r['phone']) ?>" target="_blank" style="color:var(--green-dark); font-size:.8rem; text-decoration:none;">
                                    📱 <?= htmlspecialchars($r['phone']) ?>
                                </a>
                            </td>
                            <td style="font-size:.78rem; color:var(--text-light); max-width:140px;">
                                <?= $r['notes'] ? htmlspecialchars(substr($r['notes'], 0, 60)) . '...' : '-' ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span>
                            </td>
                            <td style="white-space:nowrap;">
                                <?php if ($r['status'] === 'pending'): ?>
                                <a href="?action=confirm&id=<?= $r['id'] ?>&status=<?= $filter ?>" class="btn btn-sm" style="background:rgba(58,170,106,0.1);color:#1e7048;" onclick="return confirm('Konfirmasi reservasi ini?')">✓</a>
                                <a href="?action=cancel&id=<?= $r['id'] ?>&status=<?= $filter ?>" class="btn btn-sm btn-danger" onclick="return confirm('Batalkan reservasi ini?')">✗</a>
                                <?php endif; ?>
                                <a href="?action=delete&id=<?= $r['id'] ?>&status=<?= $filter ?>" class="btn btn-sm" style="background:rgba(224,85,85,0.07);color:var(--danger);" onclick="return confirm('Hapus permanen?')">🗑</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

</body>
</html>
