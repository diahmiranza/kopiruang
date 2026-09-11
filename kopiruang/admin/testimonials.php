<?php
require_once 'auth.php';
require_once '../config.php';

$pdo = getDB();
$activePage = 'testimonials';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name       = clean($_POST['customer_name'] ?? '');
        $rating     = (int)($_POST['rating'] ?? 5);
        $review     = clean($_POST['review'] ?? '');
        $featured   = isset($_POST['is_featured']) ? 1 : 0;
        $initial    = strtoupper(substr($name, 0, 2));

        if (!$name || !$review) {
            $msg = 'error:Nama dan ulasan wajib diisi.';
        } else {
            try {
                $pdo->prepare("INSERT INTO testimonials (customer_name, rating, review, avatar_initial, is_featured) VALUES (:n,:r,:rev,:av,:f)")
                    ->execute([':n'=>$name,':r'=>$rating,':rev'=>$review,':av'=>$initial,':f'=>$featured]);
                $msg = 'success:Ulasan berhasil ditambahkan.';
            } catch (PDOException $e) {
                $msg = 'error:Gagal menyimpan.';
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $pdo->prepare("DELETE FROM testimonials WHERE id = :id")->execute([':id' => $id]);
        $msg = 'success:Ulasan berhasil dihapus.';
    } elseif ($action === 'toggle_featured') {
        $id = (int)$_POST['id'];
        $pdo->prepare("UPDATE testimonials SET is_featured = NOT is_featured WHERE id = :id")->execute([':id' => $id]);
        $msg = 'success:Status ulasan diperbarui.';
    }

    if ($msg) {
        header('Location: testimonials.php?msg=' . urlencode($msg));
        exit;
    }
}

if (!empty($_GET['msg'])) {
    [$msgType, $msgText] = explode(':', $_GET['msg'], 2);
}

$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY is_featured DESC, created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Ulasan – Kopi Ruang Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%231a3a2e' width='100' height='100' rx='20'/><text y='.9em' font-size='70' x='10'>☕</text></svg>">
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="admin-main">
    <div class="admin-topbar">
        <div>
            <div class="topbar-title">Kelola Ulasan</div>
            <div class="topbar-subtitle"><?= count($testimonials) ?> ulasan tersimpan</div>
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

        <div style="display:grid; grid-template-columns:1fr 1.6fr; gap:1.5rem; align-items:start;">

            <!-- ADD FORM -->
            <div class="panel" style="position:sticky; top:80px;">
                <div class="panel-header">
                    <span class="panel-title">➕ Tambah Ulasan</span>
                </div>
                <div class="panel-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group-admin">
                            <label>Nama Customer</label>
                            <input type="text" name="customer_name" class="form-control" required placeholder="Rizal M.">
                        </div>
                        <div class="form-group-admin">
                            <label>Rating</label>
                            <select name="rating" class="form-control">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                <option value="<?= $i ?>" <?= $i === 5 ? 'selected' : '' ?>><?= str_repeat('★', $i) ?> (<?= $i ?>)</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group-admin">
                            <label>Ulasan</label>
                            <textarea name="review" class="form-control" rows="4" required placeholder="Tuliskan ulasan di sini..."></textarea>
                        </div>
                        <label style="display:flex; align-items:center; gap:.5rem; font-size:.85rem; cursor:pointer; margin-bottom:1rem;">
                            <input type="checkbox" name="is_featured" checked>
                            Tampilkan di website
                        </label>
                        <button type="submit" class="btn btn-primary-admin" style="width:100%; justify-content:center;">
                            Simpan Ulasan
                        </button>
                    </form>
                </div>
            </div>

            <!-- LIST -->
            <div class="panel">
                <div class="panel-header">
                    <span class="panel-title">⭐ Semua Ulasan</span>
                </div>
                <div class="panel-body" style="padding:0;">
                    <?php if (empty($testimonials)): ?>
                    <p style="text-align:center;color:#aaa;padding:2rem;">Belum ada ulasan.</p>
                    <?php else: ?>
                    <?php foreach ($testimonials as $t): ?>
                    <div style="padding:1.25rem 1.5rem; border-bottom:1px solid #f5f2ec; display:flex; gap:1rem; align-items:flex-start;">
                        <div style="width:40px; height:40px; border-radius:50%; background:var(--green-dark); color:var(--gold); display:flex; align-items:center; justify-content:center; font-size:.8rem; font-weight:700; flex-shrink:0;">
                            <?= htmlspecialchars($t['avatar_initial'] ?? substr($t['customer_name'],0,2)) ?>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:.5rem; flex-wrap:wrap;">
                                <div>
                                    <strong style="font-size:.9rem;"><?= htmlspecialchars($t['customer_name']) ?></strong>
                                    <span style="color:var(--gold); font-size:.8rem; margin-left:.4rem;"><?= str_repeat('★', $t['rating']) ?></span>
                                </div>
                                <div style="display:flex; gap:.4rem; flex-shrink:0;">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle_featured">
                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                        <button type="submit" class="btn btn-sm <?= $t['is_featured'] ? 'btn-gold' : 'btn-outline' ?>" title="Toggle tampil">
                                            <?= $t['is_featured'] ? '👁 Ditampilkan' : '👁 Disembunyikan' ?>
                                        </button>
                                    </form>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus ulasan ini?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">🗑</button>
                                    </form>
                                </div>
                            </div>
                            <p style="font-size:.82rem; color:var(--text-mid); margin-top:.35rem; font-style:italic; line-height:1.5;">
                                "<?= htmlspecialchars($t['review']) ?>"
                            </p>
                            <small style="color:#aaa;"><?= date('d M Y', strtotime($t['created_at'])) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

</body>
</html>
