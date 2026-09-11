<?php
require_once 'auth.php';
require_once '../config.php';

$pdo = getDB();
$activePage = 'menu';
$msg = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $name        = clean($_POST['name'] ?? '');
        $description = clean($_POST['description'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $price       = (int)($_POST['price'] ?? 0);
        $price_cold  = !empty($_POST['price_cold']) ? (int)$_POST['price_cold'] : null;
        $is_bestseller = isset($_POST['is_bestseller']) ? 1 : 0;
        $is_available  = isset($_POST['is_available'])  ? 1 : 0;

        if (!$name || !$category_id || !$price) {
            $msg = 'error:Nama, kategori, dan harga wajib diisi.';
        } else {
            try {
                if ($action === 'add') {
                    $stmt = $pdo->prepare("INSERT INTO menu_items (category_id, name, description, price, price_cold, is_bestseller, is_available) VALUES (:c,:n,:d,:p,:pc,:bs,:av)");
                    $stmt->execute([':c'=>$category_id,':n'=>$name,':d'=>$description,':p'=>$price,':pc'=>$price_cold,':bs'=>$is_bestseller,':av'=>$is_available]);
                    $msg = 'success:Menu berhasil ditambahkan.';
                } else {
                    $id = (int)$_POST['id'];
                    $stmt = $pdo->prepare("UPDATE menu_items SET category_id=:c, name=:n, description=:d, price=:p, price_cold=:pc, is_bestseller=:bs, is_available=:av WHERE id=:id");
                    $stmt->execute([':c'=>$category_id,':n'=>$name,':d'=>$description,':p'=>$price,':pc'=>$price_cold,':bs'=>$is_bestseller,':av'=>$is_available,':id'=>$id]);
                    $msg = 'success:Menu berhasil diperbarui.';
                }
            } catch (PDOException $e) {
                $msg = 'error:Gagal menyimpan menu.';
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        try {
            $pdo->prepare("DELETE FROM menu_items WHERE id = :id")->execute([':id' => $id]);
            $msg = 'success:Menu berhasil dihapus.';
        } catch (PDOException $e) {
            $msg = 'error:Gagal menghapus menu.';
        }
    } elseif ($action === 'toggle') {
        $id = (int)$_POST['id'];
        $pdo->prepare("UPDATE menu_items SET is_available = NOT is_available WHERE id = :id")->execute([':id' => $id]);
        $msg = 'success:Status menu diperbarui.';
    }

    if ($msg) {
        header('Location: menu.php?msg=' . urlencode($msg));
        exit;
    }
}

if (!empty($_GET['msg'])) {
    [$msgType, $msgText] = explode(':', $_GET['msg'], 2);
}

// Edit mode
$editItem = null;
if (!empty($_GET['edit'])) {
    $editItem = $pdo->prepare("SELECT * FROM menu_items WHERE id = :id");
    $editItem->execute([':id' => (int)$_GET['edit']]);
    $editItem = $editItem->fetch();
}

$categories = $pdo->query("SELECT * FROM menu_categories ORDER BY sort_order")->fetchAll();
$menuItems  = $pdo->query("
    SELECT mi.*, mc.name AS cat_name
    FROM menu_items mi
    JOIN menu_categories mc ON mi.category_id = mc.id
    ORDER BY mc.sort_order, mi.sort_order, mi.name
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu – Kopi Ruang Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%231a3a2e' width='100' height='100' rx='20'/><text y='.9em' font-size='70' x='10'>☕</text></svg>">
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="admin-main">
    <div class="admin-topbar">
        <div>
            <div class="topbar-title">Kelola Menu</div>
            <div class="topbar-subtitle"><?= count($menuItems) ?> item menu</div>
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

            <!-- ADD / EDIT FORM -->
            <div class="panel" style="position:sticky; top:80px;">
                <div class="panel-header">
                    <span class="panel-title"><?= $editItem ? '✏️ Edit Menu' : '➕ Tambah Menu' ?></span>
                    <?php if ($editItem): ?>
                    <a href="menu.php" class="btn btn-outline btn-sm">Batal</a>
                    <?php endif; ?>
                </div>
                <div class="panel-body">
                    <form method="POST" action="menu.php">
                        <input type="hidden" name="action" value="<?= $editItem ? 'edit' : 'add' ?>">
                        <?php if ($editItem): ?>
                        <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
                        <?php endif; ?>

                        <div class="form-group-admin">
                            <label>Kategori</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($editItem && $editItem['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group-admin">
                            <label>Nama Menu</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Ruang Latte"
                                   value="<?= htmlspecialchars($editItem['name'] ?? '') ?>">
                        </div>

                        <div class="form-group-admin">
                            <label>Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2"
                                      placeholder="Deskripsi singkat..."><?= htmlspecialchars($editItem['description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-grid">
                            <div class="form-group-admin">
                                <label>Harga Hot (Rp)</label>
                                <input type="number" name="price" class="form-control" required min="1000"
                                       placeholder="35000" value="<?= $editItem['price'] ?? '' ?>">
                            </div>
                            <div class="form-group-admin">
                                <label>Harga Ice (opsional)</label>
                                <input type="number" name="price_cold" class="form-control" min="1000"
                                       placeholder="38000" value="<?= $editItem['price_cold'] ?? '' ?>">
                            </div>
                        </div>

                        <div style="display:flex; gap:1.5rem; margin-bottom:1rem;">
                            <label style="display:flex; align-items:center; gap:.5rem; font-size:.85rem; cursor:pointer;">
                                <input type="checkbox" name="is_bestseller" <?= ($editItem['is_bestseller'] ?? 0) ? 'checked' : '' ?>>
                                Bestseller
                            </label>
                            <label style="display:flex; align-items:center; gap:.5rem; font-size:.85rem; cursor:pointer;">
                                <input type="checkbox" name="is_available" <?= (!$editItem || $editItem['is_available']) ? 'checked' : '' ?>>
                                Tersedia
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary-admin" style="width:100%; justify-content:center;">
                            <?= $editItem ? 'Simpan Perubahan' : 'Tambah Menu' ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- MENU LIST -->
            <div class="panel">
                <div class="panel-header">
                    <span class="panel-title">☕ Semua Menu</span>
                </div>
                <div class="table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($menuItems)): ?>
                            <tr><td colspan="5" style="text-align:center;color:#aaa;padding:2rem;">Belum ada menu.</td></tr>
                        <?php else: ?>
                            <?php foreach ($menuItems as $item): ?>
                            <tr>
                                <td>
                                    <?php if ($item['is_bestseller']): ?>
                                    <span class="badge badge-bestseller" style="margin-bottom:.25rem;">★</span><br>
                                    <?php endif; ?>
                                    <strong><?= htmlspecialchars($item['name']) ?></strong>
                                    <br><small style="color:#aaa; font-size:.75rem;"><?= htmlspecialchars(substr($item['description'], 0, 50)) ?>...</small>
                                </td>
                                <td style="font-size:.8rem; color:var(--text-light);"><?= htmlspecialchars($item['cat_name']) ?></td>
                                <td style="font-size:.85rem;">
                                    <?= formatRupiah($item['price']) ?>
                                    <?php if ($item['price_cold']): ?>
                                    <br><small style="color:#aaa;">Ice: <?= formatRupiah($item['price_cold']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="badge <?= $item['is_available'] ? 'badge-confirmed' : 'badge-cancelled' ?>" style="border:none; cursor:pointer; font-family:inherit;">
                                            <?= $item['is_available'] ? 'Aktif' : 'Nonaktif' ?>
                                        </button>
                                    </form>
                                </td>
                                <td style="white-space:nowrap;">
                                    <a href="?edit=<?= $item['id'] ?>" class="btn btn-sm btn-outline">✏️</a>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus menu ini?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">🗑</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

</body>
</html>
