<?php
require_once 'config/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        $users = getUsers();
        $found = null;
        foreach ($users as $user) {
            if (($user['username'] === $username || $user['email'] === $username)) {
                if (password_verify($password, $user['password'])) {
                    $found = $user;
                    break;
                }
            }
        }

        if ($found) {
            $_SESSION['user_id'] = $found['id'];
            $_SESSION['user_role'] = $found['role'];
            $_SESSION['user_name'] = $found['name'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Username atau password salah. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — AL-SYUKROSMART OPS</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .auth-right { overflow-y: auto; max-height: 100vh; }
        .auth-logo-wrap { margin-bottom: 28px; }
        .auth-logo-card {
            background: white;
            border-radius: 18px;
            padding: 18px 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        }
        .auth-logo-img { height: 64px; width: auto; display: block; }
    </style>
</head>
<body>
<div class="auth-wrapper">
    <!-- LEFT PANEL -->
    <div class="auth-left">
        <div class="auth-logo-wrap">
            <div class="auth-logo-card">
                <img src="logo.png" alt="Logo Perguruan Islam Al Syukro Universal" class="auth-logo-img">
            </div>
        </div>
        <div class="auth-brand">
            <h1>AL-SYUKRO<span>SMART</span> OPS</h1>
            <p>Sistem Operasional Terpadu Berbasis Web</p>
            <p style="font-size:12px; opacity:0.6; margin-top:2px;">Perguruan Islam Al-Syukro Universal</p>
        </div>
        <ul class="auth-features">
            <li><span class="icon">📦</span> Manajemen Inventaris & Peminjaman Barang</li>
            <li><span class="icon">👥</span> Sistem Manajemen Karyawan & Absensi</li>
            <li><span class="icon">🎪</span> Event Management System (EMS)</li>
            <li><span class="icon">🔧</span> Maintenance & Pengelolaan Fasilitas</li>
            <li><span class="icon">📁</span> Arsip Digital & Pelaporan Analitik</li>
        </ul>
    </div>

    <!-- RIGHT PANEL (FORM) -->
    <div class="auth-right">
        <div class="auth-form-header">
            <h2>Selamat Datang 👋</h2>
            <p>Masuk ke akun Anda untuk mengakses sistem</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error">
            <span>❌</span> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['registered'])): ?>
        <div class="alert alert-success">
            <span>✅</span> Registrasi berhasil! Silakan login dengan akun Anda.
        </div>
        <?php endif; ?>

        <form method="POST" data-validate>
            <div class="form-group">
                <label>Username / Email <span>*</span></label>
                <div class="input-wrap">
                    <span class="input-icon">👤</span>
                    <input type="text" id="username" name="username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           placeholder="Masukkan username atau email"
                           required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label>Password <span>*</span></label>
                <div class="input-wrap">
                    <span class="input-icon">🔒</span>
                    <input type="password" id="password" name="password"
                           placeholder="Masukkan password" required>
                    <button type="button" class="eye-toggle">👁️</button>
                </div>
            </div>

            <button type="submit" class="btn-primary">🔑 Masuk ke Sistem</button>
        </form>

        <div class="auth-divider">atau gunakan akun demo</div>

        <div class="demo-accounts">
            <h4>🎭 Akun Demo — Klik untuk mengisi otomatis</h4>
            <div class="demo-grid">
                <button class="demo-btn" data-user="admin" data-pass="admin123">
                    <strong>👑 Administrator</strong>
                    <span>admin / admin123</span>
                </button>
                <button class="demo-btn" data-user="adminhr" data-pass="hr123456">
                    <strong>🧑‍💼 Admin HR</strong>
                    <span>adminhr / hr123456</span>
                </button>
                <button class="demo-btn" data-user="eo" data-pass="eo123456">
                    <strong>🎪 Event Organizer</strong>
                    <span>eo / eo123456</span>
                </button>
                <button class="demo-btn" data-user="adminfas" data-pass="fas12345">
                    <strong>🏗️ Admin Fasilitas</strong>
                    <span>adminfas / fas12345</span>
                </button>
                <button class="demo-btn" data-user="teknisi" data-pass="teks1234">
                    <strong>🔧 Teknisi</strong>
                    <span>teknisi / teks1234</span>
                </button>
                <button class="demo-btn" data-user="manager" data-pass="mgr12345">
                    <strong>🎓 Manager</strong>
                    <span>manager / mgr12345</span>
                </button>
                <button class="demo-btn" data-user="adminarsip" data-pass="arsip123">
                    <strong>📁 Admin Arsip</strong>
                    <span>adminarsip / arsip123</span>
                </button>
                <button class="demo-btn" data-user="staff1" data-pass="staff123">
                    <strong>🧑 Staff</strong>
                    <span>staff1 / staff123</span>
                </button>
            </div>
        </div>

        <div class="auth-link">
            Belum punya akun? <a href="register.php">Daftar Sekarang</a>
        </div>
    </div>
</div>
<script src="js/main.js"></script>
</body>
</html>
