<?php
session_start();
require_once '../config.php';

// Sudah login
if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password tidak boleh kosong.';
    } else {
        try {
            $pdo  = getDB();
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = :u LIMIT 1");
            $stmt->execute([':u' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Login berhasil
                session_regenerate_id(true);
                $_SESSION['admin_id']   = $user['id'];
                $_SESSION['admin_name'] = $user['full_name'];
                $_SESSION['admin_user'] = $user['username'];

                $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = :id")
                    ->execute([':id' => $user['id']]);

                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Username atau password salah.';
            }
        } catch (PDOException $e) {
            // Tampilkan pesan spesifik untuk memudahkan debug
            $error = 'Koneksi database gagal: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Kopi Ruang Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%231a3a2e' width='100' height='100' rx='20'/><text y='.9em' font-size='70' x='10'>☕</text></svg>">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <div class="logo-icon-big">☕</div>
            <h1>Kopi Ruang</h1>
            <p>Admin Panel — Login untuk melanjutkan</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger">⚠ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group-admin">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control"
                       placeholder="admin" required autocomplete="username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group-admin">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="••••••••" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary-admin"
                    style="width:100%; justify-content:center; padding:.75rem; margin-top:.5rem; font-size:.9rem;">
                Masuk ke Dashboard
            </button>
        </form>

        <!-- Info login default -->
        <div style="margin-top:1.5rem; padding:1rem; background:#f9f6f0; border-radius:8px; border:1px solid #e8e0d0;">
            <p style="font-size:0.75rem; color:#888; margin-bottom:0.4rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em;">Default Login</p>
            <p style="font-size:0.82rem; color:#555; margin:0; line-height:1.6;">
                Username: <strong>admin</strong><br>
                Password: <strong>admin123</strong>
            </p>
        </div>

        <p style="text-align:center; margin-top:1rem; font-size:0.75rem;">
            <a href="../index.html" style="color:#6b8c7c; text-decoration:none;">← Kembali ke Website</a>
        </p>
    </div>
</body>
</html>
