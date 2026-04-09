<?php
require_once 'config/auth.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit(); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($username) || empty($password)) { $error = 'Username dan password wajib diisi.'; }
    else {
        $users = getUsers(); $found = null;
        foreach ($users as $user) {
            if (($user['username']===$username||$user['email']===$username) && password_verify($password,$user['password'])) { $found=$user; break; }
        }
        if ($found) {
            $_SESSION['user_id']=$found['id']; $_SESSION['user_role']=$found['role']; $_SESSION['user_name']=$found['name'];
            header('Location: dashboard.php'); exit();
        } else { $error = 'Username atau password salah.'; }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Login — AL-SYUKROSMART OPS</title>
<link rel="icon" type="image/png" href="logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --gd:#0f4525;--gm:#1a6b3c;--gl:#2d9b5a;--gp:#e8f5ed;
  --gold:#c9973a;--goldf:#f0c060;
  --white:#fff;--off:#f7faf8;--text:#1a2e22;--muted:#5a7a64;--border:#cce0d4;
}
html,body{height:100%;font-family:'DM Sans',sans-serif;background:var(--off)}
.wrap{display:flex;min-height:100vh}

/* LEFT */
.lft{width:52%;background:var(--gd);position:relative;overflow:hidden;
  display:flex;flex-direction:column;justify-content:center;align-items:center;padding:60px 56px}
.lft::before{content:'';position:absolute;inset:0;
  background:radial-gradient(circle at 20% 20%,rgba(45,155,90,.2),transparent 55%),
             radial-gradient(circle at 85% 85%,rgba(201,151,58,.15),transparent 50%)}
.geo{position:absolute;border:1px solid rgba(255,255,255,.07);border-radius:50%}
.geo:nth-child(1){width:520px;height:520px;top:-160px;right:-160px;animation:rot 45s linear infinite}
.geo:nth-child(2){width:320px;height:320px;top:-60px;right:-60px;animation:rot 28s linear infinite reverse}
.geo:nth-child(3){width:420px;height:420px;bottom:-130px;left:-130px;animation:rot 38s linear infinite}
@keyframes rot{to{transform:rotate(360deg)}}
/* dots pattern */
.lft::after{content:'';position:absolute;inset:0;
  background-image:radial-gradient(circle,rgba(255,255,255,.06) 1px,transparent 1px);
  background-size:28px 28px;pointer-events:none}

.l-inner{position:relative;z-index:1;width:100%;max-width:380px;text-align:center}

.logo-card{background:rgba(255,255,255,.95);border-radius:18px;padding:18px 28px;
  display:inline-flex;align-items:center;gap:14px;box-shadow:0 12px 40px rgba(0,0,0,.22);
  margin-bottom:36px;animation:fd .8s ease both}
.logo-card img{height:50px;width:auto}
.logo-card-t{text-align:left}
.logo-card-t strong{display:block;font-size:13px;font-weight:700;color:var(--gd);line-height:1.4}
.logo-card-t span{font-size:11px;color:var(--muted)}

.headline{font-family:'Playfair Display',serif;color:#fff;font-size:34px;line-height:1.25;
  margin-bottom:14px;animation:fd .8s .15s ease both}
.headline em{color:var(--goldf);font-style:normal}
.sub-txt{color:rgba(255,255,255,.7);font-size:13.5px;line-height:1.75;margin-bottom:36px;animation:fd .8s .25s ease both}

.feat-list{list-style:none;text-align:left;animation:fd .8s .35s ease both}
.feat-list li{display:flex;align-items:center;gap:12px;padding:11px 16px;margin-bottom:7px;
  background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.09);border-radius:10px;
  color:rgba(255,255,255,.88);font-size:13.5px;transition:.2s}
.feat-list li:hover{background:rgba(255,255,255,.12)}
.fi{width:30px;height:30px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0}

@keyframes fd{from{opacity:0;transform:translateY(-18px)}to{opacity:1;transform:translateY(0)}}
@keyframes fu{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}

/* RIGHT */
.rgt{flex:1;display:flex;align-items:center;justify-content:center;padding:40px 32px;background:#fff}
.fbox{width:100%;max-width:420px}

.fwelcome{margin-bottom:30px;animation:fu .7s .1s ease both}
.fwelcome h2{font-family:'Playfair Display',serif;font-size:28px;color:var(--gd);margin-bottom:6px}
.fwelcome p{color:var(--muted);font-size:13.5px;line-height:1.65}

.falert{padding:12px 15px;border-radius:10px;font-size:13.5px;display:flex;align-items:center;
  gap:10px;margin-bottom:20px;animation:fu .4s ease both}
.ferr{background:#fef0ef;border:1px solid #fccac6;color:#c0392b}
.fok{background:#eafaf1;border:1px solid #b7e8c9;color:#1a6b3c}

.fgrp{margin-bottom:18px}
.fgrp:nth-child(1){animation:fu .7s .15s ease both}
.fgrp:nth-child(2){animation:fu .7s .22s ease both}
.flabel{display:block;font-size:11.5px;font-weight:600;color:var(--text);
  margin-bottom:7px;letter-spacing:.4px;text-transform:uppercase}
.finwrap{position:relative}
.ficon{position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:16px;opacity:.45;pointer-events:none}
.finput{width:100%;padding:13px 44px;border:1.5px solid var(--border);border-radius:10px;
  font-size:14px;font-family:'DM Sans',sans-serif;color:var(--text);background:var(--off);
  outline:none;transition:.25s;appearance:none}
.finput:focus{border-color:var(--gl);background:#fff;box-shadow:0 0 0 4px rgba(45,155,90,.1)}
.feye{position:absolute;right:12px;top:50%;transform:translateY(-50%);
  background:none;border:none;cursor:pointer;font-size:16px;opacity:.35;transition:.2s;padding:4px}
.feye:hover{opacity:.8}

.btn-submit{width:100%;padding:14px;margin-top:6px;
  background:linear-gradient(135deg,var(--gm),var(--gl));
  color:#fff;border:none;border-radius:10px;
  font-size:15px;font-family:'DM Sans',sans-serif;font-weight:600;
  cursor:pointer;letter-spacing:.3px;transition:.3s;
  position:relative;overflow:hidden;animation:fu .7s .35s ease both}
.btn-submit::after{content:'';position:absolute;inset:0;
  background:linear-gradient(135deg,transparent,rgba(255,255,255,.18),transparent);
  transform:translateX(-100%);transition:.55s}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(26,107,60,.32)}
.btn-submit:hover::after{transform:translateX(100%)}

.divider{text-align:center;margin:20px 0;font-size:12px;color:var(--muted);
  position:relative;animation:fu .7s .4s ease both}
.divider::before,.divider::after{content:'';position:absolute;top:50%;width:38%;height:1px;background:var(--border)}
.divider::before{left:0}.divider::after{right:0}

.dbox{background:var(--off);border:1.5px solid var(--border);border-radius:12px;
  padding:14px;animation:fu .7s .45s ease both}
.dbox h4{font-size:11px;text-transform:uppercase;letter-spacing:.7px;
  color:var(--muted);margin-bottom:11px;font-weight:600}
.dgrid{display:grid;grid-template-columns:1fr 1fr;gap:7px}
.dpill{padding:8px 10px;background:#fff;border:1px solid var(--border);border-radius:8px;
  cursor:pointer;text-align:left;transition:.2s;font-family:'DM Sans',sans-serif;width:100%}
.dpill:hover{border-color:var(--gl);background:var(--gp)}
.dpill strong{display:block;font-size:12px;color:var(--gd);font-weight:600}
.dpill span{font-size:11px;color:var(--muted)}

.reglink{text-align:center;font-size:13.5px;color:var(--muted);
  margin-top:18px;animation:fu .7s .5s ease both}
.reglink a{color:var(--gm);font-weight:600;text-decoration:none}
.reglink a:hover{text-decoration:underline}

@media(max-width:880px){.lft{display:none}.rgt{padding:28px 18px}}
</style>
</head>
<body>
<div class="wrap">
  <div class="lft">
    <div class="geo"></div><div class="geo"></div><div class="geo"></div>
    <div class="l-inner">
      <div class="logo-card">
        <img src="logo.png" alt="Logo Al Syukro">
        <div class="logo-card-t">
          <strong>Perguruan Islam<br>Al Syukro Universal</strong>
          <span>Tangerang Selatan</span>
        </div>
      </div>
      <h1 class="headline">Sistem Operasional<br><em>Terpadu</em> Berbasis Web</h1>
      <p class="sub-txt">Satu platform terintegrasi untuk mengelola seluruh operasional lembaga secara efisien, akurat, dan terdokumentasi.</p>
      <ul class="feat-list">
        <li><span class="fi" style="background:rgba(41,128,185,.28)">📦</span> Inventaris &amp; Peminjaman Barang</li>
        <li><span class="fi" style="background:rgba(39,174,96,.28)">👥</span> Manajemen Karyawan &amp; Absensi</li>
        <li><span class="fi" style="background:rgba(155,89,182,.28)">🎪</span> Event Management System</li>
        <li><span class="fi" style="background:rgba(230,126,34,.28)">🔧</span> Maintenance Fasilitas</li>
        <li><span class="fi" style="background:rgba(127,140,141,.28)">📁</span> Arsip Digital &amp; Pelaporan</li>
      </ul>
    </div>
  </div>

  <div class="rgt">
    <div class="fbox">
      <div class="fwelcome">
        <h2>Selamat Datang 👋</h2>
        <p>Masuk ke akun Anda untuk mengakses sistem operasional Al-Syukro Universal.</p>
      </div>

      <?php if($error): ?><div class="falert ferr">⚠️ <?=htmlspecialchars($error)?></div><?php endif; ?>
      <?php if(isset($_GET['registered'])): ?><div class="falert fok">✅ Registrasi berhasil! Silakan login.</div><?php endif; ?>

      <form method="POST">
        <div class="fgrp">
          <label class="flabel">Username / Email</label>
          <div class="finwrap">
            <span class="ficon">👤</span>
            <input class="finput" type="text" name="username" id="username"
              value="<?=htmlspecialchars($_POST['username']??'')?>" placeholder="username atau email" required autofocus>
          </div>
        </div>
        <div class="fgrp">
          <label class="flabel">Password</label>
          <div class="finwrap">
            <span class="ficon">🔒</span>
            <input class="finput" type="password" name="password" id="pw" placeholder="password" required>
            <button type="button" class="feye" id="eyeBtn">👁️</button>
          </div>
        </div>
        <button type="submit" class="btn-submit">🔑 &nbsp;Masuk ke Sistem</button>
      </form>

      <div class="divider">atau pilih akun demo</div>

      <div class="dbox">
        <h4>🎭 Demo — Klik untuk isi otomatis</h4>
        <div class="dgrid">
          <button class="dpill" data-u="admin"      data-p="admin123"><strong>👑 Administrator</strong><span>admin123</span></button>
          <button class="dpill" data-u="adminhr"    data-p="hr123456"><strong>🧑‍💼 Admin HR</strong><span>hr123456</span></button>
          <button class="dpill" data-u="eo"         data-p="eo123456"><strong>🎪 Event Organizer</strong><span>eo123456</span></button>
          <button class="dpill" data-u="adminfas"   data-p="fas12345"><strong>🏗️ Admin Fasilitas</strong><span>fas12345</span></button>
          <button class="dpill" data-u="teknisi"    data-p="teks1234"><strong>🔧 Teknisi</strong><span>teks1234</span></button>
          <button class="dpill" data-u="manager"    data-p="mgr12345"><strong>🎓 Manager</strong><span>mgr12345</span></button>
          <button class="dpill" data-u="adminarsip" data-p="arsip123"><strong>📁 Admin Arsip</strong><span>arsip123</span></button>
          <button class="dpill" data-u="staff1"     data-p="staff123"><strong>🧑 Staff</strong><span>staff123</span></button>
        </div>
      </div>
      <p class="reglink">Belum punya akun? <a href="register.php">Daftar Sekarang →</a></p>
    </div>
  </div>
</div>
<script>
document.querySelectorAll('.dpill').forEach(b=>{
  b.addEventListener('click',()=>{document.getElementById('username').value=b.dataset.u;document.getElementById('pw').value=b.dataset.p});
});
const pw=document.getElementById('pw'),eye=document.getElementById('eyeBtn');
eye.addEventListener('click',()=>{pw.type=pw.type==='password'?'text':'password';eye.textContent=pw.type==='password'?'👁️':'🙈'});
</script>
</body>
</html>
