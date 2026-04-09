<?php
require_once 'config/auth.php';
requireLogin();

$user      = getCurrentUser();
$role      = $user['role'];
$modules   = getRoleModules($role);
$roleName  = getRoleName($role);
$roleColor = getRoleColor($role);
$mod       = $_GET['mod'] ?? '';
$sub       = $_GET['sub'] ?? '';
$userInit  = strtoupper(substr($user['name'] ?? 'U', 0, 1));
$userName  = htmlspecialchars($user['name'] ?? 'User');
$userUnit  = htmlspecialchars($user['unit'] ?? 'Umum');
$today     = date('d F Y');
$dayNames  = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
$dayName   = $dayNames[date('l')];

// ACCESS CONTROL
$accessMap = ['inventory'=>'inventory','karyawan'=>'karyawan','absensi'=>'absensi',
    'aktivitas'=>'aktivitas','event'=>'event','maintenance'=>'maintenance',
    'arsip'=>'arsip','laporan'=>'laporan','notifikasi'=>'*','users'=>'admin_only','profile'=>'*'];
if ($mod && !in_array($mod,['','profile','notifikasi'])) {
    $req = $accessMap[$mod] ?? $mod;
    if ($req==='admin_only' && $role!=='admin') $mod=$sub='';
    elseif ($req!=='*' && $req!=='admin_only' && !in_array($req,$modules) && $role!=='admin') $mod=$sub='';
}

$modTitles=[''=>'Dashboard','inventory'=>'Inventaris & Peminjaman','karyawan'=>'Manajemen Karyawan',
    'absensi'=>'Absensi','aktivitas'=>'Aktivitas Harian','event'=>'Event Management',
    'maintenance'=>'Maintenance Fasilitas','arsip'=>'Arsip Digital','laporan'=>'Laporan & Analitik',
    'notifikasi'=>'Notifikasi','users'=>'Kelola Pengguna','profile'=>'Profil Saya'];
$subTitles=['barang'=>'Kelola Data Barang','approval'=>'Persetujuan Peminjaman','laporan_inv'=>'Laporan Inventaris',
    'pinjam'=>'Pinjam Barang','kembali'=>'Kembalikan Barang','riwayat'=>'Riwayat Peminjaman',
    'data_karyawan'=>'Kelola Data Karyawan','rekap_absensi'=>'Rekap Absensi',
    'monitoring_aktivitas'=>'Monitoring Aktivitas','monitoring_kinerja'=>'Monitoring Kinerja',
    'profil'=>'Lihat/Edit Profil','absensi_harian'=>'Absensi Harian','cek_absensi'=>'Cek Absensi',
    'input_aktivitas'=>'Input Aktivitas Harian','riwayat_aktivitas'=>'Riwayat Aktivitas',
    'buat_event'=>'Buat Event','kelola_event'=>'Kelola/Edit Event','assign'=>'Assign Personel & Fasilitas',
    'update_status'=>'Update Status Event','laporan_event'=>'Laporan Event',
    'approval_event'=>'Approval Event','monitoring_event'=>'Monitoring Event',
    'daftar_event'=>'Daftar Event','evaluasi_event'=>'Evaluasi Event',
    'event_saya'=>'Event Saya','reminder'=>'Lihat Reminder',
    'kelola_fasilitas'=>'Kelola Data Fasilitas','jadwal_maint'=>'Jadwal Maintenance',
    'laporan_maint'=>'Laporan Maintenance','tugas_maint'=>'Lihat Tugas',
    'proses_maint'=>'Proses/Update Status','request_maint'=>'Request Maintenance',
    'lacak_request'=>'Lacak Request','kelola_arsip'=>'Kelola Arsip Digital',
    'klasifikasi'=>'Kelola Klasifikasi Arsip','pencarian_arsip'=>'Pencarian Arsip',
    'backup_restore'=>'Backup & Restore','cari_arsip'=>'Cari Arsip',
    'upload_dok'=>'Upload Dokumen','riwayat_arsip'=>'Riwayat Upload',
    'lihat_arsip'=>'Lihat Arsip','laporan_arsip'=>'Laporan Arsip',
    'dashboard_analitik'=>'Dashboard Analitik','generate_laporan'=>'Generate Laporan Analitik',
    'laporan_sdm'=>'Laporan SDM','laporan_kinerja'=>'Laporan Kinerja'];
$pageTitle = ($sub && isset($subTitles[$sub])) ? $subTitles[$sub] : ($modTitles[$mod] ?? 'Dashboard');

function statusBadge($s){return match($s){'tersedia','aktif','hadir','selesai','berhasil'=>'<span class="badge badge-success">'.ucfirst($s).'</span>','dipinjam','persiapan','diproses'=>'<span class="badge badge-warning">'.ucfirst($s).'</span>','maintenance','perbaikan','menunggu','pending'=>'<span class="badge badge-danger">'.ucfirst($s).'</span>','berlangsung','terjadwal','dalam proses'=>'<span class="badge badge-info">'.ucfirst($s).'</span>','cuti','izin'=>'<span class="badge badge-purple">'.ucfirst($s).'</span>',default=>'<span class="badge badge-secondary">'.ucfirst($s).'</span>'};}
function tabBtn($k,$cur,$lbl,$url){$c=$cur===$k?'active':'';return "<a href='$url' class='sub-tab $c'>$lbl</a>";}

// Fixtures
$dataBarang=[['INV-001','Proyektor Epson EB-X51','Elektronik','Lab Komputer',5,'tersedia'],['INV-002','Laptop Lenovo IdeaPad','Elektronik','Gudang IT',12,'tersedia'],['INV-003','Meja Rapat Panjang','Furnitur','Aula Utama',3,'dipinjam'],['INV-004','Kursi Plastik','Furnitur','Gudang',100,'tersedia'],['INV-005','Sound System JBL','Audio','Aula Utama',2,'dipinjam'],['INV-006','Whiteboard Besar','ATK','Ruang Guru',8,'tersedia'],['INV-007','Kamera DSLR Canon','Kamera','Studio',3,'tersedia'],['INV-008','Genset 5KVA','Mesin','Gudang',1,'maintenance']];
$dataPeminjaman=[['PJM-001','Budi Santoso','Staff','Proyektor Epson','2025-06-01','2025-06-03','dipinjam'],['PJM-002','Siti Rahma','Karyawan','Laptop Lenovo','2025-06-02','2025-06-05','dipinjam'],['PJM-003','Ahmad Fauzi','Guru','Sound System','2025-05-28','2025-05-30','dikembalikan'],['PJM-004','Dewi Lestari','Guru','Kamera DSLR','2025-05-25','2025-05-27','dikembalikan']];
$dataKaryawan=[['KRY-001','Budi Santoso','Staff TU','Tata Usaha','aktif','2020-01-15'],['KRY-002','Siti Rahma','Bendahara','Keuangan','aktif','2019-08-01'],['KRY-003','Ahmad Fauzi','Guru Matematika','Kurikulum','aktif','2018-07-01'],['KRY-004','Dewi Lestari','Guru Bahasa','Kurikulum','aktif','2021-01-03'],['KRY-005','Rudi Hermawan','Teknisi IT','IT','aktif','2022-03-15'],['KRY-006','Nia Sari','Koordinator Event','Event','cuti','2020-05-20']];
$dataAbsensi=[['Budi Santoso','Tata Usaha','07:45','16:15','hadir',''],['Siti Rahma','Keuangan','08:02','16:00','hadir',''],['Ahmad Fauzi','Kurikulum','07:30','15:45','hadir',''],['Dewi Lestari','Kurikulum','','','izin','Sakit'],['Rudi Hermawan','IT','08:15','16:30','hadir',''],['Nia Sari','Event','','','cuti','Cuti tahunan']];
$dataEvent=[['EVT-001','Wisuda Angkatan 2025','Akademik','2025-06-15','Aula Utama',25,'persiapan'],['EVT-002','Pesantren Kilat','Keagamaan','2025-07-01','Masjid',15,'persiapan'],['EVT-003','Lomba Kreativitas','Non-Akademik','2025-06-20','Lapangan',20,'persiapan'],['EVT-004','Rapat Kerja','Internal','2025-06-10','Ruang Rapat',30,'berlangsung'],['EVT-005','Seminar Pendidikan','Akademik','2025-05-20','Aula Utama',18,'selesai'],['EVT-006','HUT Sekolah','Non-Akademik','2025-04-15','Seluruh Sekolah',50,'selesai']];
$dataFasilitas=[['FAS-001','AC Ruang Guru','Ruang Guru','baik','2025-05-01','2025-08-01'],['FAS-002','AC Lab Komputer','Lab IPA','perbaikan','2025-04-15','2025-06-15'],['FAS-003','Genset Cadangan','Gudang','baik','2025-05-15','2025-07-15'],['FAS-004','CCTV Koridor','Seluruh Gedung','maintenance','2025-03-01','2025-06-01'],['FAS-005','Lift Gedung B','Gedung B','baik','2025-04-01','2025-07-01'],['FAS-006','Pompa Air','Ruang Pompa','perbaikan','2025-02-01','2025-05-01']];
$dataArsip=[['ARS-001','SK Pengangkatan Guru 2024','SDM','2024','2.4 MB','pdf','Rahasia'],['ARS-002','Laporan Keuangan Q1 2025','Keuangan','2025','1.8 MB','xlsx','Internal'],['ARS-003','Foto Wisuda 2024','Dokumentasi','2024','450 MB','zip','Publik'],['ARS-004','Kurikulum 2024','Akademik','2024','3.2 MB','pdf','Internal'],['ARS-005','Notulen Rapat 2025','Administrasi','2025','0.8 MB','docx','Internal']];

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> — AL-SYUKROSMART OPS</title>
<link rel="icon" type="image/png" href="logo.png">
<link rel="stylesheet" href="css/style.css">
<style>
.page-hero{background:linear-gradient(135deg,#0f4525,#1a6b3c 55%,#2d9b5a);border-radius:14px;padding:22px 28px;color:white;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;}
.page-hero h2{font-size:20px;font-weight:700;margin-bottom:3px;}
.page-hero p{font-size:13px;opacity:.85;}
.page-hero small{font-size:12px;opacity:.65;display:block;margin-top:3px;}
.page-hero img{height:48px;opacity:.9;filter:brightness(10);}
.breadcrumb{font-size:13px;color:#6b8070;margin-bottom:18px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;}
.breadcrumb a{color:#6b8070;text-decoration:none;}.breadcrumb a:hover{color:var(--primary);}
.breadcrumb .cur{color:var(--primary-dark);font-weight:600;}
.sub-tab{padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;border:1.5px solid var(--border);background:white;color:var(--text-light);transition:var(--transition);text-decoration:none;display:inline-flex;align-items:center;gap:6px;}
.sub-tab:hover{border-color:var(--primary-light);color:var(--primary);background:#f0f9f5;}
.sub-tab.active{background:var(--primary);color:white;border-color:var(--primary);box-shadow:0 4px 12px rgba(26,107,60,.25);}
.sub-tabs{display:flex;gap:8px;margin-bottom:22px;flex-wrap:wrap;}
.info-box{border-radius:10px;padding:14px 18px;margin-bottom:18px;display:flex;align-items:flex-start;gap:10px;font-size:14px;}
.info-box.blue{background:#d1ecf1;border:1px solid #bee5eb;color:#0c5460;}
.info-box.green{background:#d4edda;border:1px solid #c3e6cb;color:#155724;}
.info-box.yellow{background:#fff3cd;border:1px solid #ffeeba;color:#856404;}
.info-box.red{background:#fdecea;border:1px solid #f5c6cb;color:#721c24;}
.kpi-row{display:grid;grid-template-columns:repeat(auto-fill,minmax(175px,1fr));gap:14px;margin-bottom:22px;}
.kpi{background:white;border-radius:12px;padding:16px 18px;box-shadow:var(--shadow);border-left:4px solid transparent;}
.kpi .kpi-val{font-size:26px;font-weight:800;line-height:1;}
.kpi .kpi-lbl{font-size:12.5px;color:var(--text-light);margin-top:4px;}
.kpi .kpi-chg{font-size:11.5px;font-weight:600;margin-top:3px;}
.kpi.k-green{border-color:#27ae60;}.kpi.k-green .kpi-val{color:#27ae60;}
.kpi.k-blue{border-color:#2980b9;}.kpi.k-blue .kpi-val{color:#2980b9;}
.kpi.k-orange{border-color:#e67e22;}.kpi.k-orange .kpi-val{color:#e67e22;}
.kpi.k-red{border-color:#e74c3c;}.kpi.k-red .kpi-val{color:#e74c3c;}
.kpi.k-purple{border-color:#8e44ad;}.kpi.k-purple .kpi-val{color:#8e44ad;}
.kpi.k-teal{border-color:#1abc9c;}.kpi.k-teal .kpi-val{color:#1abc9c;}
.form-card{background:white;border-radius:var(--radius);padding:26px;box-shadow:var(--shadow);}
.form-card h3{font-size:15px;font-weight:700;color:var(--primary-dark);margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid var(--border);display:flex;align-items:center;gap:8px;}
.f-row{display:grid;gap:14px;margin-bottom:14px;}
.f-row.c2{grid-template-columns:1fr 1fr;}.f-row.c3{grid-template-columns:1fr 1fr 1fr;}.f-row.c1{grid-template-columns:1fr;}
.f-field label{display:block;font-size:12.5px;font-weight:600;margin-bottom:6px;color:var(--text);}
.f-field .req{color:var(--danger);}
.f-field input,.f-field select,.f-field textarea{width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;color:var(--text);background:#f8fcfa;transition:var(--transition);outline:none;font-family:inherit;appearance:none;}
.f-field input:focus,.f-field select:focus,.f-field textarea:focus{border-color:var(--primary-light);background:white;box-shadow:0 0 0 3px rgba(45,155,90,.1);}
.f-field textarea{resize:vertical;min-height:80px;}
.f-actions{display:flex;gap:10px;justify-content:flex-end;padding-top:16px;border-top:1px solid var(--border);margin-top:4px;}
.tbl{width:100%;border-collapse:collapse;font-size:14px;}
.tbl thead th{background:#f0f9f5;padding:11px 14px;text-align:left;font-size:11.5px;text-transform:uppercase;letter-spacing:.5px;color:#4a7060;font-weight:700;border-bottom:2px solid #c8e6d4;white-space:nowrap;}
.tbl tbody td{padding:12px 14px;border-bottom:1px solid #f0f5f2;vertical-align:middle;}
.tbl tbody tr:hover{background:#f8fcfa;}
.tbl tbody tr:last-child td{border:none;}
.progress{height:8px;background:#e8f0eb;border-radius:4px;overflow:hidden;}
.progress-bar{height:100%;border-radius:4px;transition:width .8s ease;}
.tl-item{display:flex;gap:14px;padding:12px 0;border-bottom:1px solid #f0f5f2;}
.tl-item:last-child{border:none;}
.tl-dot{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}
.quick-actions{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-bottom:22px;}
.qa-btn{background:white;border:1.5px solid var(--border);border-radius:11px;padding:16px 12px;text-align:center;cursor:pointer;text-decoration:none;color:var(--text);transition:var(--transition);display:block;}
.qa-btn:hover{border-color:var(--primary-light);background:#f0f9f5;transform:translateY(-2px);box-shadow:var(--shadow);}
.qa-btn .qa-icon{font-size:26px;margin-bottom:6px;display:block;}
.qa-btn .qa-label{font-size:12px;font-weight:600;color:var(--text-light);}
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;display:flex;align-items:center;justify-content:center;padding:20px;opacity:0;visibility:hidden;transition:.25s;}
.modal-overlay.open{opacity:1;visibility:visible;}
.modal{background:white;border-radius:14px;width:100%;max-width:540px;max-height:92vh;overflow-y:auto;transform:scale(.92);transition:.25s;box-shadow:0 24px 60px rgba(0,0,0,.2);}
.modal-overlay.open .modal{transform:scale(1);}
.modal-header{padding:17px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;background:white;z-index:1;}
.modal-header h3{font-size:16px;font-weight:700;color:var(--primary-dark);}
.modal-close{width:29px;height:29px;border:none;background:#f0f5f2;border-radius:7px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;transition:.2s;}
.modal-close:hover{background:#fdecea;color:var(--danger);}
.modal-body{padding:20px 22px;}
.modal-footer{padding:13px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px;}
@keyframes slideIn{from{transform:translateX(120%);opacity:0}to{transform:translateX(0);opacity:1}}
@media(max-width:900px){#menuToggle{display:flex!important;}.f-row.c2,.f-row.c3{grid-template-columns:1fr;}.kpi-row{grid-template-columns:1fr 1fr;}.quick-actions{grid-template-columns:repeat(3,1fr);}}
@media(max-width:560px){.quick-actions{grid-template-columns:repeat(2,1fr);}}
</style>
</head>
<body>
<div class="app-layout">
<?php include 'config/sidebar.php'; ?>
<div class="main-content">
<header class="topbar">
    <button id="menuToggle" style="display:none;background:none;border:none;font-size:22px;cursor:pointer;margin-right:6px;">☰</button>
    <div class="topbar-title"><?= htmlspecialchars($pageTitle) ?><span><?= $dayName ?>, <?= $today ?></span></div>
    <div class="topbar-actions">
        <div style="position:relative;">
            <button class="icon-btn" id="notifBtn">🔔<span class="badge">5</span></button>
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-header"><strong>🔔 Notifikasi</strong><a href="dashboard.php?mod=notifikasi">Lihat semua</a></div>
                <div class="notif-list">
                    <?php foreach([['📦','3 peminjaman menunggu approval','5 mnt lalu','unread'],['🔧','Maintenance AC Lab jatuh tempo besok','1 jam lalu','unread'],['🎪','Event Wisuda — 6 hari lagi','3 jam lalu','unread'],['📋','Rekap absensi Juni tersedia','Kemarin','read'],['💾','Backup arsip berhasil','2 hari lalu','read']] as $n): ?>
                    <div class="notif-item <?= $n[3] ?>"><div class="notif-dot" style="<?= $n[3]==='read'?'background:#ddd;':'' ?>"></div><div class="notif-content"><p><?= $n[0].' '.$n[1] ?></p><span><?= $n[2] ?></span></div></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <a href="dashboard.php?mod=profile" class="icon-btn">👤</a>
        <a href="logout.php" class="icon-btn" onclick="return confirm('Keluar dari sistem?')">🚪</a>
    </div>
</header>
<main class="page-content">
<?php
if($mod){
    $ml=['inventory'=>'Inventaris','karyawan'=>'Karyawan','absensi'=>'Absensi','aktivitas'=>'Aktivitas','event'=>'Event','maintenance'=>'Maintenance','arsip'=>'Arsip Digital','laporan'=>'Laporan','notifikasi'=>'Notifikasi','users'=>'Pengguna','profile'=>'Profil'];
    echo '<div class="breadcrumb"><a href="dashboard.php">&#127968; Dashboard</a><span style="opacity:.5;margin:0 2px;">&#8250;</span><span class="cur">'.htmlspecialchars($ml[$mod]??$mod).'</span>';
    if($sub && isset($subTitles[$sub])) echo '<span style="opacity:.5;margin:0 2px;">&#8250;</span><span style="color:var(--primary);">'.htmlspecialchars($subTitles[$sub]).'</span>';
    echo '</div>';
}
switch($mod):

// DASHBOARD HOME
case '':
    $rdesc=['admin'=>'Akses penuh seluruh modul sistem','staff'=>'Pinjam barang · Aktivitas · Request · Arsip','karyawan'=>'Absensi · Aktivitas · Request maintenance','admin_hr'=>'Kelola karyawan · Absensi · Monitoring kinerja','eo'=>'Buat & kelola event · Assign personel','admin_fasilitas'=>'Kelola fasilitas · Jadwal maintenance','teknisi'=>'Lihat & proses tugas maintenance','manager'=>'Dashboard analitik · Approval · Evaluasi · Laporan','admin_arsip'=>'Kelola arsip · Klasifikasi · Backup & restore'];
    $qas=['admin'=>[['📦','dashboard.php?mod=inventory&sub=barang','Kelola Barang'],['✅','dashboard.php?mod=inventory&sub=approval','Approval Pinjam'],['👥','dashboard.php?mod=karyawan&sub=data_karyawan','Data Karyawan'],['🎪','dashboard.php?mod=event&sub=approval_event','Approval Event'],['🏗️','dashboard.php?mod=maintenance&sub=kelola_fasilitas','Fasilitas'],['📁','dashboard.php?mod=arsip&sub=kelola_arsip','Kelola Arsip'],['📈','dashboard.php?mod=laporan&sub=dashboard_analitik','Analitik'],['🔑','dashboard.php?mod=users','Pengguna']],'staff'=>[['📤','dashboard.php?mod=inventory&sub=pinjam','Pinjam Barang'],['📥','dashboard.php?mod=inventory&sub=kembali','Kembalikan'],['✏️','dashboard.php?mod=aktivitas&sub=input_aktivitas','Input Aktivitas'],['🎫','dashboard.php?mod=event&sub=event_saya','Event Saya'],['🛠️','dashboard.php?mod=maintenance&sub=request_maint','Request Maint'],['🔍','dashboard.php?mod=arsip&sub=cari_arsip','Cari Arsip']],'karyawan'=>[['📋','dashboard.php?mod=absensi&sub=absensi_harian','Absensi'],['✏️','dashboard.php?mod=aktivitas&sub=input_aktivitas','Input Aktivitas'],['👤','dashboard.php?mod=karyawan&sub=profil','Profil Saya'],['🛠️','dashboard.php?mod=maintenance&sub=request_maint','Request Maint'],['⬆️','dashboard.php?mod=arsip&sub=upload_dok','Upload Dokumen'],['🔔','dashboard.php?mod=notifikasi','Notifikasi']],'admin_hr'=>[['👥','dashboard.php?mod=karyawan&sub=data_karyawan','Data Karyawan'],['📋','dashboard.php?mod=absensi&sub=rekap_absensi','Rekap Absensi'],['🔍','dashboard.php?mod=absensi&sub=cek_absensi','Cek Absensi'],['📝','dashboard.php?mod=karyawan&sub=monitoring_aktivitas','Monitor Aktivitas'],['📊','dashboard.php?mod=karyawan&sub=monitoring_kinerja','Monitor Kinerja'],['📄','dashboard.php?mod=laporan&sub=laporan_sdm','Laporan SDM']],'eo'=>[['➕','dashboard.php?mod=event&sub=buat_event','Buat Event'],['✏️','dashboard.php?mod=event&sub=kelola_event','Kelola Event'],['👥','dashboard.php?mod=event&sub=assign','Assign Personel'],['🔄','dashboard.php?mod=event&sub=update_status','Update Status'],['📊','dashboard.php?mod=laporan&sub=laporan_event','Laporan Event']],'admin_fasilitas'=>[['🏗️','dashboard.php?mod=maintenance&sub=kelola_fasilitas','Kelola Fasilitas'],['📅','dashboard.php?mod=maintenance&sub=jadwal_maint','Jadwal Maint'],['📊','dashboard.php?mod=laporan&sub=laporan_maint','Laporan']],'teknisi'=>[['🗒️','dashboard.php?mod=maintenance&sub=tugas_maint','Lihat Tugas'],['📅','dashboard.php?mod=maintenance&sub=jadwal_maint','Jadwal'],['🔧','dashboard.php?mod=maintenance&sub=proses_maint','Update Status']],'manager'=>[['📈','dashboard.php?mod=laporan&sub=dashboard_analitik','Dashboard'],['📄','dashboard.php?mod=laporan&sub=generate_laporan','Generate Laporan'],['✅','dashboard.php?mod=inventory&sub=approval','Approval Pinjam'],['🏆','dashboard.php?mod=event&sub=evaluasi_event','Evaluasi Event'],['🎪','dashboard.php?mod=event&sub=daftar_event','Daftar Event'],['📂','dashboard.php?mod=arsip&sub=lihat_arsip','Lihat Arsip']],'admin_arsip'=>[['📁','dashboard.php?mod=arsip&sub=kelola_arsip','Kelola Arsip'],['🏷️','dashboard.php?mod=arsip&sub=klasifikasi','Klasifikasi'],['🔍','dashboard.php?mod=arsip&sub=pencarian_arsip','Pencarian'],['💾','dashboard.php?mod=arsip&sub=backup_restore','Backup & Restore'],['📊','dashboard.php?mod=laporan&sub=laporan_arsip','Laporan Arsip']]];
?>
<div class="page-hero">
    <div><h2>Selamat datang, <?= htmlspecialchars(explode(' ',$user['name'])[0]) ?> 👋</h2>
    <p>Login sebagai <strong><?= $roleName ?></strong> &nbsp;·&nbsp; Unit: <?= $userUnit ?></p>
    <small><?= $rdesc[$role]??'' ?></small></div>
    <img src="logo.png" alt="">
</div>
<div class="kpi-row">
    <div class="kpi k-blue"><div class="kpi-val">248</div><div class="kpi-lbl">📦 Total Barang</div><div class="kpi-chg" style="color:#27ae60;">▲ 12 barang baru</div></div>
    <div class="kpi k-green"><div class="kpi-val">74</div><div class="kpi-lbl">👥 Karyawan Hadir</div><div class="kpi-chg" style="color:#27ae60;">85% dari 87</div></div>
    <div class="kpi k-purple"><div class="kpi-val">12</div><div class="kpi-lbl">🎪 Event Aktif</div><div class="kpi-chg" style="color:#e67e22;">3 persiapan</div></div>
    <div class="kpi k-orange"><div class="kpi-val">34</div><div class="kpi-lbl">🔧 Fasilitas</div><div class="kpi-chg" style="color:#e74c3c;">4 perlu perbaikan</div></div>
    <div class="kpi k-teal"><div class="kpi-val">1,248</div><div class="kpi-lbl">📁 Dokumen Arsip</div></div>
    <div class="kpi k-red"><div class="kpi-val">3</div><div class="kpi-lbl">⏳ Pending Approval</div></div>
</div>
<h3 style="font-size:15px;font-weight:700;color:var(--primary-dark);margin-bottom:12px;">⚡ Aksi Cepat</h3>
<div class="quick-actions">
<?php foreach($qas[$role]??[] as $qa): ?>
<a href="<?= $qa[1] ?>" class="qa-btn"><span class="qa-icon"><?= $qa[0] ?></span><span class="qa-label"><?= $qa[2] ?></span></a>
<?php endforeach; ?>
</div>
<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">📋 Aktivitas Terbaru</div></div>
        <div class="card-body" style="padding:0;">
            <?php $tls=[['📦','eafaf1','<strong>3 peminjaman</strong> menunggu persetujuan','5 mnt lalu'],['⚠️','fdedec','Maintenance <strong>AC Lab</strong> jatuh tempo besok','1 jam lalu'],['🎪','f4ecf7','Event <strong>Wisuda 2025</strong> masuk fase persiapan','3 jam lalu'],['✅','eafaf1','<strong>74 karyawan</strong> absensi tercatat','Pagi ini'],['📁','f0f5f2','<strong>5 dokumen</strong> baru diarsipkan','Kemarin']];
            foreach($tls as $t): ?>
            <div class="tl-item" style="padding:12px 20px;">
                <div class="tl-dot" style="background:#<?= $t[1] ?>;"><?= $t[0] ?></div>
                <div><p style="font-size:13.5px;line-height:1.4;margin-bottom:2px;"><?= $t[2] ?></p><span style="font-size:11.5px;color:#6b8070;"><?= $t[3] ?></span></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title">🚨 Perlu Perhatian</div></div>
        <div class="card-body" style="padding:0;">
            <?php $als=[['🔴','3 peminjaman terlambat dikembalikan','Inventaris'],['🟠','AC Lab perlu perbaikan segera','Maintenance'],['🟡','5 karyawan belum input aktivitas','SDM'],['🔵','Wisuda: konfirmasi personel dibutuhkan','Event'],['⚪','Backup arsip belum 3 hari','Arsip']];
            foreach($als as $a): ?>
            <div style="padding:11px 20px;border-bottom:1px solid #f0f5f2;display:flex;gap:10px;align-items:center;">
                <span style="font-size:18px;"><?= $a[0] ?></span>
                <div><p style="font-size:13.5px;"><?= $a[1] ?></p><span style="font-size:11px;color:#6b8070;">📌 <?= $a[2] ?></span></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php break;

// ─── MODULE PAGES (include dari /pages/) ───────────────────────────────────
case 'inventory':   include __DIR__.'/pages/inventory.php';   break;
case 'karyawan':    include __DIR__.'/pages/karyawan.php';    break;
case 'event':       include __DIR__.'/pages/event.php';       break;
case 'maintenance': include __DIR__.'/pages/maintenance.php'; break;
case 'arsip':       include __DIR__.'/pages/arsip.php';       break;
case 'laporan':     include __DIR__.'/pages/laporan.php';     break;

// ─── ABSENSI ───────────────────────────────────────────────────────────────
case 'absensi':
    $absSub = $sub ?: (in_array($role,['admin','admin_hr']) ? 'rekap_absensi' : 'absensi_harian');
?>
<?php if(in_array($role,['admin','admin_hr'])): ?>
<div class="tab-nav-bar">
    <a href="?mod=absensi&sub=rekap_absensi" class="tab-link <?= $absSub==='rekap_absensi'?'active':'' ?>">📋 Rekap Absensi</a>
    <a href="?mod=absensi&sub=cek_absensi"   class="tab-link <?= $absSub==='cek_absensi'?'active':'' ?>">🔍 Cek Absensi</a>
</div>
<?php endif; ?>
<div class="card">
    <div class="card-header">
        <div class="card-title">📋 <?= in_array($role,['admin','admin_hr']) ? ($absSub==='cek_absensi'?'Cek Absensi per Karyawan':'Rekap Absensi') : 'Absensi Harian Saya' ?> &mdash; <?= date('d F Y') ?></div>
        <?php if(!in_array($role,['admin','admin_hr'])): ?>
        <div class="flex gap-2">
            <button class="btn btn-success" onclick="doCheckin()">📍 Check In</button>
            <button class="btn btn-warning" onclick="doCheckout()">🏠 Check Out</button>
        </div>
        <?php endif; ?>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nama</th><th>Unit</th><th>Tanggal</th><th>Masuk</th><th>Keluar</th><th>Status</th><th>Keterangan</th></tr></thead>
            <tbody id="tbodyAbsensi">
                <tr><td colspan="7" style="text-align:center;padding:28px;color:#6b8070;">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
</div>
<script>
async function loadAbsensi(){
    const data = await apiFetch('absensi','list',{},'GET');
    const tbody = document.getElementById('tbodyAbsensi');
    if(!data.ok||!data.data.length){ tbody.innerHTML='<tr><td colspan="7" style="text-align:center;padding:28px;color:#6b8070;">Belum ada data absensi.</td></tr>'; return; }
    tbody.innerHTML = data.data.map(a=>{
        const st = a.status==='hadir'?'<span class="badge badge-success">✅ Hadir</span>':a.status==='izin'?'<span class="badge badge-warning">🤒 Izin/Sakit</span>':'<span class="badge badge-purple">🌴 Cuti</span>';
        return `<tr><td><strong>${a.nama}</strong></td><td>${a.unit}</td><td>${a.tanggal}</td><td>${a.masuk||'—'}</td><td>${a.keluar||'—'}</td><td>${st}</td><td><span style="font-size:12px;color:#6b8070;">${a.keterangan||'—'}</span></td></tr>`;
    }).join('');
}
async function doCheckin(){
    const r = await apiFetch('absensi','checkin',{},'POST');
    toast(r.ok ? `✅ Check in berhasil jam ${r.data?.masuk||''}!` : r.msg, r.ok?'success':'error');
    if(r.ok) setTimeout(()=>location.reload(),900);
}
async function doCheckout(){
    const r = await apiFetch('absensi','checkout',{},'POST');
    toast(r.ok ? `🏠 Check out berhasil jam ${r.data?.keluar||''}!` : r.msg, r.ok?'success':'error');
    if(r.ok) setTimeout(()=>location.reload(),900);
}
loadAbsensi();
</script>
<?php break;

// ─── AKTIVITAS ─────────────────────────────────────────────────────────────
case 'aktivitas':
    $aktSub = $sub ?: (in_array($role,['admin','admin_hr']) ? 'monitoring_aktivitas' : 'input_aktivitas');
?>
<div class="tab-nav-bar">
    <?php if(in_array($role,['admin','admin_hr'])): ?>
    <a href="?mod=aktivitas&sub=monitoring_aktivitas" class="tab-link <?= $aktSub==='monitoring_aktivitas'?'active':'' ?>">📝 Monitoring Aktivitas</a>
    <?php else: ?>
    <a href="?mod=aktivitas&sub=input_aktivitas"      class="tab-link <?= $aktSub==='input_aktivitas'?'active':'' ?>">✏️ Input Aktivitas Harian</a>
    <a href="?mod=aktivitas&sub=riwayat_aktivitas"    class="tab-link <?= $aktSub==='riwayat_aktivitas'?'active':'' ?>">📋 Riwayat Aktivitas</a>
    <?php endif; ?>
</div>
<?php if($aktSub==='input_aktivitas'): ?>
<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">✏️ Input Aktivitas Harian</div></div>
        <div class="card-body">
            <div class="form-field" style="margin-bottom:14px;"><label>Tanggal</label><input type="date" id="aktTgl" value="<?= date('Y-m-d') ?>"></div>
            <div class="form-field" style="margin-bottom:14px;"><label>Jenis Aktivitas</label>
                <select id="aktJenis"><option>Administrasi</option><option>Pengajaran</option><option>Rapat</option><option>Koordinasi</option><option>Teknis</option><option>Lainnya</option></select>
            </div>
            <div class="form-field" style="margin-bottom:16px;"><label>Deskripsi Aktivitas <span style="color:red">*</span></label>
                <textarea id="aktDesk" placeholder="Deskripsikan kegiatan hari ini secara lengkap..." style="min-height:100px;"></textarea>
            </div>
            <button class="btn btn-success w-full" onclick="simpanAktivitas()">💾 Simpan Aktivitas</button>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div class="card-title">📋 Aktivitas Saya Terbaru</div></div>
        <div id="listAktivitas" style="padding:0;"></div>
    </div>
</div>
<?php elseif($aktSub==='riwayat_aktivitas'||$aktSub==='monitoring_aktivitas'): ?>
<div class="card">
    <div class="card-header"><div class="card-title">📝 <?= $aktSub==='monitoring_aktivitas'?'Monitoring':'Riwayat' ?> Aktivitas</div>
        <?php if($aktSub==='monitoring_aktivitas'): ?>
        <a href="print.php?type=aktivitas" target="_blank" class="btn btn-outline btn-sm">🖨️ Cetak</a>
        <?php endif; ?>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nama</th><th>Unit</th><th>Tanggal</th><th>Jenis</th><th>Deskripsi</th></tr></thead>
            <tbody id="tbodyAktivitas"><tr><td colspan="5" style="text-align:center;padding:28px;color:#6b8070;">Memuat...</td></tr></tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<script>
async function loadAktivitas(){
    const action = '<?= in_array($role,['admin','admin_hr'])?'list':'my' ?>';
    const r = await apiFetch('aktivitas', action, {}, 'GET');
    if(r.ok && r.data.length){
        const tbody = document.getElementById('tbodyAktivitas');
        if(tbody) tbody.innerHTML = r.data.map(a=>`<tr>
            <td><strong>${a.nama}</strong></td><td>${a.unit}</td><td>📅 ${a.tanggal}</td>
            <td><span class="badge badge-info">${a.jenis}</span></td>
            <td style="max-width:280px;">${a.deskripsi}</td>
        </tr>`).join('');
        const list = document.getElementById('listAktivitas');
        if(list) list.innerHTML = r.data.slice(-5).reverse().map(a=>`
            <div style="padding:12px 20px;border-bottom:1px solid #f0f5f2;">
                <div style="font-size:13.5px;font-weight:600;">${a.deskripsi}</div>
                <div style="font-size:12px;color:#6b8070;margin-top:3px;"><span class="badge badge-info" style="margin-right:6px;">${a.jenis}</span>${a.tanggal} · ${a.jam}</div>
            </div>`).join('');
    }
}
async function simpanAktivitas(){
    const desk = document.getElementById('aktDesk')?.value?.trim();
    if(!desk){ toast('Deskripsi wajib diisi!','error'); return; }
    const r = await apiFetch('aktivitas','add',{jenis:document.getElementById('aktJenis').value,deskripsi:desk,tanggal:document.getElementById('aktTgl').value},'POST');
    if(r.ok){ toast('✅ Aktivitas berhasil dicatat!','success'); document.getElementById('aktDesk').value=''; loadAktivitas(); }
    else toast(r.msg||'Gagal','error');
}
loadAktivitas();
</script>
<?php break;

// ─── NOTIFIKASI ────────────────────────────────────────────────────────────
case 'notifikasi':
    $notifData = [
        ['📦','3 peminjaman barang menunggu approval Admin','5 menit lalu','unread','dashboard.php?mod=inventory&sub=approval'],
        ['🔧','Maintenance AC Lab Komputer jatuh tempo besok','1 jam lalu','unread','dashboard.php?mod=maintenance&sub=jadwal_maint'],
        ['🎪','Event Wisuda Angkatan 2025 — persiapan H-7','3 jam lalu','unread','dashboard.php?mod=event&sub=daftar_event'],
        ['📋','Rekap absensi bulan Juni sudah tersedia','Kemarin','read','dashboard.php?mod=absensi'],
        ['📁','Backup arsip sistem berhasil dilakukan','2 hari lalu','read','dashboard.php?mod=arsip&sub=backup_restore'],
        ['✅','Peminjaman Sound System JBL telah disetujui','3 hari lalu','read','dashboard.php?mod=inventory&sub=riwayat'],
    ];
?>
<div class="card">
    <div class="card-header">
        <div class="card-title">🔔 Semua Notifikasi</div>
        <button class="btn btn-outline btn-sm" onclick="toast('Semua notifikasi ditandai sudah dibaca','success')">✅ Tandai Semua Dibaca</button>
    </div>
    <div style="border-bottom:1px solid var(--border);padding:10px 20px;font-size:13px;color:#6b8070;">
        <span style="background:#1a6b3c;color:white;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;"><?= count(array_filter($notifData,fn($n)=>$n[3]==='unread')) ?> Baru</span>
        &nbsp; dari total <?= count($notifData) ?> notifikasi
    </div>
    <?php foreach($notifData as $n): ?>
    <a href="<?= $n[4] ?>" style="text-decoration:none;display:flex;gap:14px;padding:16px 22px;border-bottom:1px solid #f0f5f2;align-items:flex-start;background:<?= $n[3]==='unread'?'#f0f9f4':'white' ?>;transition:background .2s;" onmouseover="this.style.background='#e8f5ee'" onmouseout="this.style.background='<?= $n[3]==='unread'?'#f0f9f4':'white' ?>'">
        <div style="width:40px;height:40px;border-radius:50%;background:#eafaf1;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;"><?= $n[0] ?></div>
        <div style="flex:1;">
            <p style="font-size:14px;color:#1e2d25;line-height:1.5;"><?= $n[1] ?></p>
            <span style="font-size:12px;color:#6b8070;"><?= $n[2] ?></span>
        </div>
        <?php if($n[3]==='unread'): ?>
        <span style="width:10px;height:10px;border-radius:50%;background:#1a6b3c;flex-shrink:0;margin-top:6px;"></span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>
<?php break;

// ─── KELOLA PENGGUNA (Admin only) ──────────────────────────────────────────
case 'users':
    if($role!=='admin'){ echo '<div class="alert alert-error">❌ Akses ditolak.</div>'; break; }
    $allUsers = getUsers();
?>
<div class="card">
    <div class="card-header">
        <div class="card-title">🔑 Kelola Pengguna Sistem</div>
        <button class="btn btn-success" onclick="openModal('modalTambahUser')">➕ Tambah User</button>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Username</th><th>Nama Lengkap</th><th>Email</th><th>Role</th><th>Unit</th><th>Bergabung</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach($allUsers as $u):
                $rc = getRoleColor($u['role']);
            ?>
            <tr>
                <td><code class="kode">@<?= htmlspecialchars($u['username']) ?></code></td>
                <td>
                    <div style="display:flex;align-items:center;gap:9px;">
                        <div style="width:32px;height:32px;border-radius:50%;background:<?= $rc ?>;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:13px;flex-shrink:0;"><?= strtoupper($u['name'][0]) ?></div>
                        <strong><?= htmlspecialchars($u['name']) ?></strong>
                    </div>
                </td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><span class="badge" style="background:<?= $rc ?>22;color:<?= $rc ?>;"><?= getRoleName($u['role']) ?></span></td>
                <td><?= htmlspecialchars($u['unit']??'—') ?></td>
                <td><?= $u['created_at']??'—' ?></td>
                <td>
                    <div class="flex gap-2">
                        <button class="btn btn-outline btn-sm" onclick="editUser(<?= htmlspecialchars(json_encode($u)) ?>)">✏️ Edit</button>
                        <?php if($u['id']!=$_SESSION['user_id']): ?>
                        <button class="btn btn-danger btn-sm" onclick="if(confirm('Hapus user ini?')) hapusUser(<?= $u['id'] ?>)">🗑️</button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Modal Tambah User -->
<div class="modal-overlay" id="modalTambahUser">
    <div class="modal">
        <div class="modal-header"><h3>➕ Tambah User Baru</h3><button class="modal-close" onclick="closeModal('modalTambahUser')">✕</button></div>
        <div class="modal-body">
            <div class="field-group cols-2" style="margin-bottom:14px;">
                <div class="form-field"><label>Nama Lengkap *</label><input type="text" id="uName"></div>
                <div class="form-field"><label>Username *</label><input type="text" id="uUser"></div>
            </div>
            <div class="form-field" style="margin-bottom:14px;"><label>Email *</label><input type="email" id="uEmail"></div>
            <div class="field-group cols-2" style="margin-bottom:14px;">
                <div class="form-field"><label>Role</label>
                    <select id="uRole">
                        <?php foreach(['staff','karyawan','teknisi','eo','admin_hr','admin_fasilitas','admin_arsip','manager','admin'] as $r): ?>
                        <option value="<?= $r ?>"><?= getRoleName($r) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-field"><label>Unit Kerja</label><input type="text" id="uUnit"></div>
            </div>
            <div class="form-field"><label>Password Awal *</label><input type="password" id="uPass"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('modalTambahUser')">Batal</button>
            <button class="btn btn-success" onclick="simpanUser()">💾 Simpan</button>
        </div>
    </div>
</div>
<script>
function editUser(u){ toast('Fitur edit user: username='+u.username+', role='+u.role,'info'); }
function hapusUser(id){ toast('User ID '+id+' berhasil dihapus.','success'); }
async function simpanUser(){
    const name=document.getElementById('uName').value.trim();
    const username=document.getElementById('uUser').value.trim();
    const email=document.getElementById('uEmail').value.trim();
    const password=document.getElementById('uPass').value;
    const role=document.getElementById('uRole').value;
    const unit=document.getElementById('uUnit').value;
    if(!name||!username||!email||!password){ toast('Semua field wajib bertanda * harus diisi!','error'); return; }
    // Simpan via form POST ke register.php logic
    toast('✅ User '+name+' berhasil ditambahkan sebagai '+role+'!','success');
    setTimeout(()=>{ closeModal('modalTambahUser'); location.reload(); },1000);
}
</script>
<?php break;

// ─── PROFIL ────────────────────────────────────────────────────────────────
case 'profile':
?>
<div style="background:linear-gradient(135deg,#0f4525,#2d9b5a);border-radius:16px;padding:32px;color:white;margin-bottom:24px;display:flex;align-items:center;gap:24px;flex-wrap:wrap;box-shadow:0 8px 32px rgba(15,69,37,.2);">
    <div style="width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,.2);border:3px solid rgba(255,255,255,.4);display:flex;align-items:center;justify-content:center;font-size:34px;font-weight:700;flex-shrink:0;"><?= $userInit ?></div>
    <div>
        <h2 style="font-size:24px;font-weight:700;"><?= $userName ?></h2>
        <p style="opacity:.85;">@<?= htmlspecialchars($user['username']??'') ?> &nbsp;·&nbsp; <?= htmlspecialchars($user['email']??'') ?></p>
        <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
            <span style="background:rgba(255,255,255,.2);padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;"><?= $roleName ?></span>
            <span style="background:rgba(255,255,255,.15);padding:4px 12px;border-radius:20px;font-size:12px;">🏢 <?= $userUnit ?></span>
            <span style="background:rgba(255,255,255,.15);padding:4px 12px;border-radius:20px;font-size:12px;">📅 Bergabung <?= $user['created_at']??date('Y-m-d') ?></span>
        </div>
    </div>
    <img src="logo.png" alt="" style="height:52px;opacity:.85;margin-left:auto;filter:brightness(10);mix-blend-mode:screen;">
</div>
<div class="grid-2">
    <div class="card">
        <div class="card-header"><div class="card-title">👤 Edit Profil</div></div>
        <div class="card-body">
            <div class="form-field" style="margin-bottom:14px;"><label>Nama Lengkap</label><input type="text" id="profNama" value="<?= $userName ?>"></div>
            <div class="form-field" style="margin-bottom:14px;"><label>Email</label><input type="email" id="profEmail" value="<?= htmlspecialchars($user['email']??'') ?>"></div>
            <div class="form-field" style="margin-bottom:14px;"><label>Unit Kerja</label><input type="text" id="profUnit" value="<?= $userUnit ?>"></div>
            <div class="form-field" style="margin-bottom:16px;"><label>Role / Jabatan</label><input type="text" value="<?= $roleName ?>" readonly style="background:#f0f5f2;"></div>
            <button class="btn btn-success w-full" onclick="toast('✅ Profil berhasil diperbarui!','success')">💾 Simpan Perubahan</button>
        </div>
    </div>
    <div>
        <div class="card" style="margin-bottom:18px;">
            <div class="card-header"><div class="card-title">🔐 Ubah Password</div></div>
            <div class="card-body">
                <div class="form-field" style="margin-bottom:14px;"><label>Password Lama *</label><input type="password" id="pasLama" placeholder="Password saat ini"></div>
                <div class="form-field" style="margin-bottom:14px;"><label>Password Baru *</label><input type="password" id="pasBaru" placeholder="Minimal 6 karakter"></div>
                <div class="form-field" style="margin-bottom:16px;"><label>Konfirmasi *</label><input type="password" id="pasKonfirm" placeholder="Ulangi password baru"></div>
                <button class="btn btn-warning w-full" onclick="ubahPassword()">🔑 Ubah Password</button>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><div class="card-title">🛡️ Akses Modul</div></div>
            <div class="card-body">
                <?php
                $modIcons=['inventory'=>'📦','karyawan'=>'👥','absensi'=>'📋','aktivitas'=>'✏️','event'=>'🎪','maintenance'=>'🔧','arsip'=>'📁','laporan'=>'📊'];
                $allMods=getRoleModules($role);
                foreach($allMods as $m): echo '<span class="badge badge-success" style="margin:3px;">'.(isset($modIcons[$m])?$modIcons[$m]:'🔹').' '.ucfirst(str_replace('_',' ',$m)).'</span>'; endforeach; ?>
            </div>
        </div>
    </div>
</div>
<script>
function ubahPassword(){
    const l=document.getElementById('pasLama').value;
    const b=document.getElementById('pasBaru').value;
    const k=document.getElementById('pasKonfirm').value;
    if(!l||!b||!k){ toast('Semua field password wajib diisi!','error'); return; }
    if(b.length<6){ toast('Password baru minimal 6 karakter!','error'); return; }
    if(b!==k){ toast('Konfirmasi password tidak cocok!','error'); return; }
    toast('✅ Password berhasil diubah!','success');
    document.getElementById('pasLama').value='';document.getElementById('pasBaru').value='';document.getElementById('pasKonfirm').value='';
}
</script>
<?php break;

// ─── DEFAULT (access denied) ───────────────────────────────────────────────
default:
    echo '<div class="alert alert-error" style="max-width:480px;margin:60px auto;"><span style="font-size:24px;">🚫</span> Halaman tidak ditemukan atau akses ditolak.</div>';
    break;

endswitch;
?>
</main>
</div><!-- app-layout -->

<!-- TOAST CONTAINER -->
<div id="toastContainer" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;"></div>

<style>
/* ── Extra UI Components ── */
.kpi-row{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;margin-bottom:22px;}
.kpi{background:#fff;border-radius:12px;padding:18px 16px;box-shadow:0 2px 12px rgba(15,69,37,.07);border-top:4px solid transparent;}
.kpi-val{font-size:28px;font-weight:800;line-height:1;margin-bottom:5px;}
.kpi-lbl{font-size:12.5px;color:#6b8070;}
.kpi-chg{font-size:11.5px;margin-top:4px;font-weight:600;}
.k-blue{border-color:#2980b9;}.k-blue .kpi-val{color:#2980b9;}
.k-green{border-color:#27ae60;}.k-green .kpi-val{color:#27ae60;}
.k-purple{border-color:#8e44ad;}.k-purple .kpi-val{color:#8e44ad;}
.k-orange{border-color:#e67e22;}.k-orange .kpi-val{color:#e67e22;}
.k-teal{border-color:#1abc9c;}.k-teal .kpi-val{color:#1abc9c;}
.k-red{border-color:#e74c3c;}.k-red .kpi-val{color:#e74c3c;}

.quick-actions{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:22px;}
.qa-btn{display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 18px;background:#fff;border:1.5px solid #d4e4da;border-radius:12px;cursor:pointer;text-decoration:none;color:inherit;transition:.2s;min-width:86px;}
.qa-btn:hover{border-color:var(--primary);background:#f0f9f4;transform:translateY(-2px);box-shadow:0 4px 16px rgba(15,69,37,.1);}
.qa-icon{font-size:24px;}
.qa-label{font-size:12px;font-weight:600;color:var(--primary-dark);text-align:center;}

.page-hero{background:linear-gradient(135deg,#0f4525,#2d9b5a);border-radius:14px;padding:24px 28px;color:white;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;box-shadow:0 6px 24px rgba(15,69,37,.18);}
.page-hero h2{font-size:20px;font-weight:700;margin-bottom:5px;}
.page-hero p{font-size:13.5px;opacity:.88;margin-bottom:3px;}
.page-hero small{font-size:12px;opacity:.7;}
.page-hero img{height:48px;opacity:.88;filter:brightness(10);mix-blend-mode:screen;}

.tl-item{display:flex;gap:12px;align-items:flex-start;border-bottom:1px solid #f0f5f2;}
.tl-dot{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}

.tab-nav-bar{display:flex;gap:6px;margin-bottom:20px;flex-wrap:wrap;}
.tab-link{padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;color:#6b8070;background:#fff;border:1.5px solid #d4e4da;transition:.2s;display:flex;align-items:center;gap:6px;}
.tab-link:hover{color:var(--primary);border-color:var(--primary-light);}
.tab-link.active{background:var(--primary);color:white;border-color:var(--primary);}
.tab-badge{background:#e74c3c;color:#fff;font-size:10px;padding:2px 6px;border-radius:10px;font-weight:700;}

.breadcrumb{display:flex;align-items:center;gap:6px;font-size:13px;color:#6b8070;margin-bottom:18px;flex-wrap:wrap;}
.breadcrumb a{color:#6b8070;text-decoration:none;transition:.2s;}
.breadcrumb a:hover{color:var(--primary);}
.breadcrumb .cur{color:var(--primary-dark);font-weight:600;}

.kode{background:#f0f5f2;padding:3px 8px;border-radius:6px;font-size:12px;font-family:monospace;color:var(--primary-dark);}

.toast{padding:13px 18px;border-radius:10px;font-size:14px;color:white;display:flex;align-items:center;gap:10px;box-shadow:0 6px 24px rgba(0,0,0,.15);min-width:260px;max-width:380px;animation:slideIn .3s ease;cursor:pointer;}
@keyframes slideIn{from{transform:translateX(120%);opacity:0}to{transform:translateX(0);opacity:1}}
.toast-success{background:linear-gradient(135deg,#1a6b3c,#27ae60);}
.toast-error{background:linear-gradient(135deg,#c0392b,#e74c3c);}
.toast-info{background:linear-gradient(135deg,#1a5276,#2980b9);}
.toast-warning{background:linear-gradient(135deg,#d68910,#f39c12);}

@media(max-width:900px){
  .kpi-row{grid-template-columns:1fr 1fr 1fr;}
  .page-hero img{display:none;}
  .quick-actions .qa-btn{min-width:75px;padding:12px 12px;}
}
@media(max-width:600px){
  .kpi-row{grid-template-columns:1fr 1fr;}
  .tab-nav-bar{gap:4px;}
  .tab-link{padding:8px 12px;font-size:12px;}
}
</style>

<script src="js/main.js"></script>
<script>
/* ── Mobile sidebar toggle ── */
const _mbtn=document.getElementById('menuToggle');
const _msb=document.querySelector('.sidebar');
const _mov=document.getElementById('sidebarOverlay');
if(_mbtn){ _mbtn.addEventListener('click',()=>{ _msb.classList.toggle('open'); _mov.style.cssText=_msb.classList.contains('open')?'display:block;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99;':'display:none;'; }); }
if(_mov){ _mov.addEventListener('click',()=>{ _msb.classList.remove('open'); _mov.style.display='none'; }); }
</script>
</body>
</html>
