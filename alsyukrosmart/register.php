<?php
require_once 'config/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'staff';
    $unit = trim($_POST['unit'] ?? '');

    if (empty($name) || empty($username) || empty($email) || empty($password)) {
        $error = 'Semua field bertanda * wajib diisi.';
    } elseif ($password !== $confirm) {
        $error = 'Password dan konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        $users = getUsers();
        $exists = false;
        foreach ($users as $u) {
            if ($u['username'] === $username || $u['email'] === $email) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            $error = 'Username atau email sudah terdaftar.';
        } else {
            $newUser = [
                'id' => count($users) + 1,
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'unit' => $unit ?: 'Umum',
                'created_at' => date('Y-m-d')
            ];
            $users[] = $newUser;
            saveUsers($users);
            header('Location: index.php?registered=1');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi — AL-SYUKROSMART OPS</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .auth-right { width: 560px; padding: 40px 50px; }
        @media(max-width:900px){ .auth-right{ width:100%; padding:30px; } }
    </style>
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-left">
        <div class="auth-logo-wrap" style="margin-bottom:28px;">
            <div class="auth-logo-card" style="background:white;border-radius:18px;padding:18px 28px;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 8px 32px rgba(0,0,0,0.15);">
                <img src="logo.png" alt="Logo Al Syukro Universal" style="height:64px;width:auto;display:block;">
            </div>
        </div>
        <div class="auth-brand">
            <h1>AL-SYUKRO<span>SMART</span> OPS</h1>
            <p>Buat Akun Baru</p>
        </div>
        <ul class="auth-features" style="margin-top: 40px;">
            <li><span class="icon">🔐</span> Akses sesuai role & wewenang</li>
            <li><span class="icon">📊</span> Dashboard personal terintegrasi</li>
            <li><span class="icon">🔔</span> Notifikasi real-time</li>
            <li><span class="icon">📱</span> Akses dari mana saja</li>
            <li><span class="icon">🔒</span> Keamanan data terjamin</li>
        </ul>
    </div>

    <div class="auth-right">
        <div class="auth-form-header">
            <h2>Buat Akun Baru ✨</h2>
            <p>Daftarkan diri Anda ke dalam sistem</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" data-validate>
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Lengkap <span>*</span></label>
                    <div class="input-wrap">
                        <span class="input-icon">👤</span>
                        <input type="text" name="name" placeholder="Nama lengkap Anda"
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Username <span>*</span></label>
                    <div class="input-wrap">
                        <span class="input-icon">🆔</span>
                        <input type="text" name="username" placeholder="Username unik"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Email <span>*</span></label>
                <div class="input-wrap">
                    <span class="input-icon">✉️</span>
                    <input type="email" name="email" placeholder="email@alsyukro.sch.id"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Role / Jabatan <span>*</span></label>
                    <div class="input-wrap">
                        <span class="input-icon">🎭</span>
                        <select name="role" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="staff" <?= ($_POST['role'] ?? '') === 'staff' ? 'selected' : '' ?>>Staff</option>
                            <option value="karyawan" <?= ($_POST['role'] ?? '') === 'karyawan' ? 'selected' : '' ?>>Karyawan</option>
                            <option value="teknisi" <?= ($_POST['role'] ?? '') === 'teknisi' ? 'selected' : '' ?>>Teknisi</option>
                            <option value="eo" <?= ($_POST['role'] ?? '') === 'eo' ? 'selected' : '' ?>>Event Organizer</option>
                            <option value="admin_hr" <?= ($_POST['role'] ?? '') === 'admin_hr' ? 'selected' : '' ?>>Admin HR</option>
                            <option value="admin_fasilitas" <?= ($_POST['role'] ?? '') === 'admin_fasilitas' ? 'selected' : '' ?>>Admin Fasilitas</option>
                            <option value="admin_arsip" <?= ($_POST['role'] ?? '') === 'admin_arsip' ? 'selected' : '' ?>>Admin Arsip</option>
                            <option value="manager" <?= ($_POST['role'] ?? '') === 'manager' ? 'selected' : '' ?>>Manager/Pimpinan</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Unit Kerja</label>
                    <div class="input-wrap">
                        <span class="input-icon">🏢</span>
                        <input type="text" name="unit" placeholder="Contoh: Kurikulum"
                               value="<?= htmlspecialchars($_POST['unit'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Password <span>*</span></label>
                    <div class="input-wrap">
                        <span class="input-icon">🔒</span>
                        <input type="password" name="password" id="password"
                               placeholder="Min. 6 karakter" required>
                        <button type="button" class="eye-toggle">👁️</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password <span>*</span></label>
                    <div class="input-wrap">
                        <span class="input-icon">🔐</span>
                        <input type="password" name="confirm_password"
                               placeholder="Ulangi password" required>
                        <button type="button" class="eye-toggle">👁️</button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary">📝 Daftar Sekarang</button>
        </form>

        <div class="auth-link" style="margin-top:16px;">
            Sudah punya akun? <a href="index.php">Login di sini</a>
        </div>
    </div>
</div>
<script src="js/main.js"></script>
</body>
</html>
