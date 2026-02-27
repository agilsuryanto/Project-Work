<?php
require_once 'config/auth.php';
requireLogin();

$user    = getCurrentUser();
$role    = $user['role'];
$modules = getRoleModules($role);
$roleName  = getRoleName($role);
$roleColor = getRoleColor($role);
$mod     = $_GET['mod'] ?? '';   // modul utama
$sub     = $_GET['sub'] ?? '';   // sub-halaman dalam modul
$userInitial = strtoupper(substr($user['name'], 0, 1));

// Untuk backward-compat tetap ada $currentModule
$currentModule = $mod;

// ===== AKSES KONTROL sesuai diagram =====
// Mapping mod → modul yang diperlukan di getRoleModules()
$modRequired = [
    'inventory'   => 'inventory',
    'karyawan'    => 'karyawan',
    'absensi'     => 'absensi',
    'aktivitas'   => 'aktivitas',
    'event'       => 'event',
    'maintenance' => 'maintenance',
    'arsip'       => 'arsip',
    'laporan'     => 'laporan',
    'notifikasi'  => '*',   // semua bisa akses
    'users'       => 'admin_only',
    'profile'     => '*',
];

if ($mod && $mod !== '' && $mod !== 'profile' && $mod !== 'notifikasi') {
    $req = $modRequired[$mod] ?? $mod;
    if ($req === 'admin_only' && $role !== 'admin') {
        $mod = $sub = $currentModule = '';
    } elseif ($req !== '*' && $req !== 'admin_only' && !in_array($req, $modules) && $role !== 'admin') {
        $mod = $sub = $currentModule = '';
    }
}

// ===== JUDUL HALAMAN (mod + sub) =====
$subTitles = [
    // Inventory
    'barang'      => 'Kelola Data Barang',
    'approval'    => 'Persetujuan Peminjaman',
    'laporan_inv' => 'Laporan Inventaris',
    'pinjam'      => 'Pinjam Barang',
    'kembali'     => 'Kembalikan Barang',
    'riwayat'     => 'Riwayat Peminjaman',
    // Karyawan / HR
    'data_karyawan'        => 'Kelola Data Karyawan',
    'rekap_absensi'        => 'Rekap Absensi',
    'monitoring_aktivitas' => 'Monitoring Aktivitas',
    'monitoring_kinerja'   => 'Monitoring Kinerja',
    'profil'               => 'Lihat / Edit Profil',
    // Absensi
    'absensi_harian' => 'Absensi Harian',
    'cek_absensi'    => 'Cek Absensi',
    // Aktivitas
    'input_aktivitas'   => 'Input Aktivitas Harian',
    'riwayat_aktivitas' => 'Riwayat Aktivitas',
    // Event
    'buat_event'      => 'Buat Event',
    'kelola_event'    => 'Kelola / Edit Event',
    'assign'          => 'Assign Personel & Fasilitas',
    'update_status'   => 'Update Status Event',
    'laporan_event'   => 'Laporan Event',
    'approval_event'  => 'Approval Event',
    'monitoring_event'=> 'Monitoring Event',
    'daftar_event'    => 'Daftar Event',
    'evaluasi_event'  => 'Evaluasi Event',
    'event_saya'      => 'Event Saya',
    'reminder'        => 'Lihat Reminder',
    // Maintenance
    'kelola_fasilitas' => 'Kelola Data Fasilitas',
    'jadwal_maint'     => 'Jadwal Maintenance',
    'laporan_maint'    => 'Laporan Maintenance',
    'tugas_maint'      => 'Lihat Tugas',
    'proses_maint'     => 'Proses / Update Status',
    'request_maint'    => 'Request Maintenance',
    'lacak_request'    => 'Lacak Request',
    // Arsip
    'kelola_arsip'    => 'Kelola Arsip Digital',
    'klasifikasi'     => 'Kelola Klasifikasi Arsip',
    'pencarian_arsip' => 'Pencarian Arsip',
    'backup_restore'  => 'Backup & Restore',
    'cari_arsip'      => 'Cari Arsip',
    'upload_dok'      => 'Upload Dokumen',
    'riwayat_arsip'   => 'Riwayat Upload',
    'lihat_arsip'     => 'Lihat Arsip',
    'laporan_arsip'   => 'Laporan Arsip',
    // Laporan
    'dashboard_analitik' => 'Dashboard Analitik',
    'generate_laporan'   => 'Generate Laporan Analitik',
    'semua_laporan'      => 'Semua Laporan',
    'laporan_sdm'        => 'Laporan SDM',
    'laporan_kinerja'    => 'Laporan Kinerja',
];

$modTitles = [
    ''            => 'Dashboard',
    'inventory'   => 'Inventaris & Peminjaman Barang',
    'karyawan'    => 'Manajemen Karyawan',
    'absensi'     => 'Absensi',
    'aktivitas'   => 'Aktivitas Harian',
    'event'       => 'Event Management System',
    'maintenance' => 'Maintenance Fasilitas',
    'arsip'       => 'Arsip Digital',
    'laporan'     => 'Laporan & Analitik',
    'notifikasi'  => 'Notifikasi',
    'users'       => 'Kelola Pengguna',
    'profile'     => 'Profil Saya',
];

if ($sub && isset($subTitles[$sub])) {
    $pageTitle = $subTitles[$sub];
} else {
    $pageTitle = $modTitles[$mod] ?? 'Dashboard';
}

// Handle form success/action
$actionMsg = '';
$actionType = '';
if (isset($_GET['action'])) {
    $actionMsg  = 'Data berhasil disimpan!';
    $actionType = 'success';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> — AL-SYUKROSMART OPS</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="app-layout">

    <?php include 'config/sidebar.php'; ?>

    <div class="main-content">
        <!-- TOPBAR -->
        <header class="topbar">
            <button id="menuToggle" style="display:none; background:none; border:none; font-size:22px; cursor:pointer; margin-right:8px;">☰</button>
            <div class="topbar-title">
                <?= $pageTitle ?>
                <span id="currentDate"></span>
            </div>
            <div class="topbar-actions">
                <div style="position:relative;">
                    <button class="icon-btn" id="notifBtn" title="Notifikasi">
                        🔔
                        <span class="badge">5</span>
                    </button>
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header">
                            <strong>Notifikasi</strong>
                            <a href="#">Tandai semua dibaca</a>
                        </div>
                        <div class="notif-list">
                            <div class="notif-item unread">
                                <div class="notif-dot"></div>
                                <div class="notif-content">
                                    <p>📦 <strong>3 barang</strong> menunggu persetujuan peminjaman</p>
                                    <span>5 menit lalu</span>
                                </div>
                            </div>
                            <div class="notif-item unread">
                                <div class="notif-dot"></div>
                                <div class="notif-content">
                                    <p>🔧 Maintenance AC Lab komputer <strong>jatuh tempo besok</strong></p>
                                    <span>1 jam lalu</span>
                                </div>
                            </div>
                            <div class="notif-item unread">
                                <div class="notif-dot"></div>
                                <div class="notif-content">
                                    <p>🎪 Event Wisuda <strong>3 hari lagi</strong></p>
                                    <span>3 jam lalu</span>
                                </div>
                            </div>
                            <div class="notif-item">
                                <div class="notif-dot" style="background:#ddd;"></div>
                                <div class="notif-content">
                                    <p>📋 Rekap absensi bulan ini sudah tersedia</p>
                                    <span>Kemarin</span>
                                </div>
                            </div>
                            <div class="notif-item">
                                <div class="notif-dot" style="background:#ddd;"></div>
                                <div class="notif-content">
                                    <p>📁 Backup arsip berhasil dilakukan</p>
                                    <span>2 hari lalu</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="dashboard.php?mod=profile" class="icon-btn" title="Profil">👤</a>
                <a href="logout.php" class="icon-btn" title="Keluar" onclick="return confirm('Keluar dari sistem?')">🚪</a>
            </div>
        </header>

        <main class="page-content">
            <?php if ($actionMsg): ?>
            <div class="alert alert-<?= $actionType ?>">
                <?= $actionType === 'success' ? '✅' : '❌' ?> <?= $actionMsg ?>
            </div>
            <?php endif; ?>

            <?php
            // ==========================================
            // RENDER MODULE CONTENT (mod + sub routing)
            // hal.8=Inventory, hal.4=HR, hal.7=Event
            // hal.3=Maintenance, hal.2=Arsip
            // ==========================================

            // Breadcrumb trail
            if ($mod && $mod !== '') {
                $modNames = ['inventory'=>'Inventaris','karyawan'=>'SDM & Karyawan','absensi'=>'Absensi',
                    'aktivitas'=>'Aktivitas','event'=>'Event','maintenance'=>'Maintenance',
                    'arsip'=>'Arsip Digital','laporan'=>'Laporan','users'=>'Pengguna',
                    'profile'=>'Profil','notifikasi'=>'Notifikasi'];
                echo '<nav style="font-size:13px;color:#6b8070;margin-bottom:18px;display:flex;align-items:center;gap:6px;">';
                echo '<a href="dashboard.php" style="color:#6b8070;text-decoration:none;">&#127968; Dashboard</a>';
                echo '<span>&#8250;</span>';
                echo '<span style="color:var(--primary-dark);font-weight:600;">'.htmlspecialchars($modNames[$mod] ?? $mod).'</span>';
                if ($sub && isset($subTitles[$sub])) {
                    echo '<span>&#8250;</span><span style="color:var(--primary);">'.htmlspecialchars($subTitles[$sub]).'</span>';
                }
                echo '</nav>';
            }

            switch ($mod) {

            case 'inventory':
            // ─────────────────────────────────────────
            // MODUL INVENTORY – Hal.8 Flowchart
            // Admin:  Kelola Data Barang | Persetujuan | Laporan
            // Staff:  Pinjam Barang | Kembalikan | Riwayat
            // ─────────────────────────────────────────
            ?>
            <!-- ===== INVENTORY MODULE ===== -->
            <div class="stats-grid">
                <div class="stat-card c-blue">
                    <div class="stat-icon c-blue" style="border:none;">📦</div>
                    <div class="stat-info">
                        <h3 class="stat-number" data-target="248">248</h3>
                        <p>Total Barang</p>
                        <div class="stat-change up">▲ 12 bulan ini</div>
                    </div>
                </div>
                <div class="stat-card c-green">
                    <div class="stat-icon c-green" style="border:none;">✅</div>
                    <div class="stat-info">
                        <h3 class="stat-number" data-target="186">186</h3>
                        <p>Barang Tersedia</p>
                    </div>
                </div>
                <div class="stat-card c-orange">
                    <div class="stat-icon c-orange" style="border:none;">🔄</div>
                    <div class="stat-info">
                        <h3 class="stat-number" data-target="47">47</h3>
                        <p>Sedang Dipinjam</p>
                    </div>
                </div>
                <div class="stat-card c-red">
                    <div class="stat-icon c-red" style="border:none;">⏰</div>
                    <div class="stat-info">
                        <h3 class="stat-number" data-target="3">3</h3>
                        <p>Perlu Approval</p>
                        <div class="stat-change down">▲ Segera diproses</div>
                    </div>
                </div>
            </div>

            <?php
            // Tentukan tab aktif berdasarkan $sub (dari sidebar link)
            // Sesuai diagram hal.8:
            //   Admin  → tab: barang | approval | laporan_inv
            //   Staff  → tab: pinjam | kembali | riwayat
            $invActiveTab = $sub ?: ($role === 'staff' ? 'pinjam' : 'barang');
            ?>
            <div class="tab-group">
                <div class="tab-nav">
                    <?php if (in_array($role, ['admin','admin_fasilitas'])): ?>
                    <button class="tab-btn <?= $invActiveTab==='barang'?'active':'' ?>" data-tab="tab-barang" onclick="location.href='dashboard.php?mod=inventory&sub=barang'">📦 Kelola Data Barang</button>
                    <button class="tab-btn <?= $invActiveTab==='approval'?'active':'' ?>" data-tab="tab-approval" onclick="location.href='dashboard.php?mod=inventory&sub=approval'">✅ Persetujuan Peminjaman</button>
                    <button class="tab-btn <?= $invActiveTab==='laporan_inv'?'active':'' ?>" data-tab="tab-laporan-inv" onclick="location.href='dashboard.php?mod=inventory&sub=laporan_inv'">📄 Laporan Inventaris</button>
                    <?php elseif ($role === 'manager'): ?>
                    <button class="tab-btn <?= $invActiveTab==='approval'?'active':'' ?>" data-tab="tab-approval" onclick="location.href='dashboard.php?mod=inventory&sub=approval'">✅ Approval Peminjaman</button>
                    <button class="tab-btn <?= $invActiveTab==='laporan_inv'?'active':'' ?>" data-tab="tab-laporan-inv" onclick="location.href='dashboard.php?mod=inventory&sub=laporan_inv'">📄 Laporan Inventaris</button>
                    <?php elseif ($role === 'staff'): ?>
                    <button class="tab-btn <?= $invActiveTab==='pinjam'?'active':'' ?>" data-tab="tab-pinjam" onclick="location.href='dashboard.php?mod=inventory&sub=pinjam'">📤 Pinjam Barang</button>
                    <button class="tab-btn <?= $invActiveTab==='kembali'?'active':'' ?>" data-tab="tab-kembali" onclick="location.href='dashboard.php?mod=inventory&sub=kembali'">📥 Kembalikan Barang</button>
                    <button class="tab-btn <?= $invActiveTab==='riwayat'?'active':'' ?>" data-tab="tab-riwayat" onclick="location.href='dashboard.php?mod=inventory&sub=riwayat'">📋 Lihat Riwayat Peminjaman</button>
                    <?php else: ?>
                    <button class="tab-btn active" data-tab="tab-riwayat">📋 Riwayat Peminjaman</button>
                    <?php endif; ?>
                </div>

                <div id="tab-barang" class="tab-pane active">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">📦 Daftar Inventaris</div>
                            <div class="flex gap-2">
                                <div class="search-input-wrap" style="width:240px;">
                                    <span class="search-icon">🔍</span>
                                    <input type="text" id="tableSearch" placeholder="Cari barang...">
                                </div>
                                <?php if ($role === 'admin' || $role === 'admin_fasilitas'): ?>
                                <button class="btn btn-primary-sm" data-modal="modalTambahBarang">+ Tambah</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th data-sort>Kode</th>
                                        <th data-sort>Nama Barang</th>
                                        <th data-sort>Kategori</th>
                                        <th data-sort>Lokasi</th>
                                        <th>Stok</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $items = [
                                        ['INV-001','Proyektor Epson EB-X51','Elektronik','Lab Komputer',5,'tersedia'],
                                        ['INV-002','Laptop Lenovo IdeaPad','Elektronik','Gudang IT',12,'tersedia'],
                                        ['INV-003','Meja Rapat Panjang','Furnitur','Aula Utama',3,'dipinjam'],
                                        ['INV-004','Kursi Plastik','Furnitur','Gudang','100','tersedia'],
                                        ['INV-005','Sound System JBL','Audio','Aula Utama',2,'dipinjam'],
                                        ['INV-006','Whiteboard Besar','ATK','Ruang Guru',8,'tersedia'],
                                        ['INV-007','Kamera DSLR Canon','Kamera','Studio',3,'tersedia'],
                                        ['INV-008','Genset 5KVA','Mesin','Gudang',1,'maintenance'],
                                    ];
                                    foreach ($items as $item):
                                        $statusBadge = match($item[5]) {
                                            'tersedia' => '<span class="badge badge-success">✅ Tersedia</span>',
                                            'dipinjam' => '<span class="badge badge-warning">🔄 Dipinjam</span>',
                                            'maintenance' => '<span class="badge badge-danger">🔧 Maintenance</span>',
                                            default => '<span class="badge badge-secondary">-</span>'
                                        };
                                    ?>
                                    <tr>
                                        <td><code style="font-size:12px;background:#f0f4f8;padding:3px 7px;border-radius:5px;"><?= $item[0] ?></code></td>
                                        <td><strong><?= $item[1] ?></strong></td>
                                        <td><?= $item[2] ?></td>
                                        <td><span style="font-size:12px;">📍 <?= $item[3] ?></span></td>
                                        <td><strong><?= $item[4] ?></strong></td>
                                        <td><?= $statusBadge ?></td>
                                        <td>
                                            <div class="flex gap-2">
                                                <?php if ($item[5] === 'tersedia'): ?>
                                                <button class="btn btn-info btn-sm" onclick="openModal('modalPinjam')">📤 Pinjam</button>
                                                <?php endif; ?>
                                                <?php if ($role === 'admin' || $role === 'admin_fasilitas'): ?>
                                                <button class="btn btn-outline btn-sm">✏️</button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="tab-peminjaman" class="tab-pane">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">🔄 Riwayat Peminjaman</div>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th><th>Peminjam</th><th>Barang</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th><th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $loans = [
                                        ['PJM-001','Budi Santoso','Proyektor Epson','2025-06-01','2025-06-03','dipinjam'],
                                        ['PJM-002','Siti Rahma','Laptop Lenovo','2025-06-02','2025-06-05','dipinjam'],
                                        ['PJM-003','Ahmad Fauzi','Sound System','2025-05-28','2025-05-30','dikembalikan'],
                                        ['PJM-004','Dewi Lestari','Kamera DSLR','2025-05-25','2025-05-27','dikembalikan'],
                                    ];
                                    foreach ($loans as $l):
                                        $sb = $l[5]==='dipinjam' ? '<span class="badge badge-warning">🔄 Dipinjam</span>' : '<span class="badge badge-success">✅ Dikembalikan</span>';
                                    ?>
                                    <tr>
                                        <td><code style="font-size:12px;background:#f0f4f8;padding:3px 7px;border-radius:5px;"><?= $l[0] ?></code></td>
                                        <td><?= $l[1] ?></td>
                                        <td><?= $l[2] ?></td>
                                        <td><?= $l[3] ?></td>
                                        <td><?= $l[4] ?></td>
                                        <td><?= $sb ?></td>
                                        <td>
                                            <?php if ($l[5]==='dipinjam'): ?>
                                            <button class="btn btn-success btn-sm">📥 Kembalikan</button>
                                            <?php else: ?>
                                            <button class="btn btn-outline btn-sm">👁️ Detail</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="tab-approval" class="tab-pane">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">⏳ Menunggu Persetujuan</div>
                            <span class="badge badge-danger">3 Pending</span>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr><th>Peminjam</th><th>Barang</th><th>Kep.</th><th>Tgl</th><th>Aksi</th></tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Rudi Hermawan</strong><br><span class="text-sm text-muted">Staff IT</span></td>
                                        <td>Proyektor Epson x2</td>
                                        <td>3 hari</td>
                                        <td>2025-06-08</td>
                                        <td>
                                            <div class="flex gap-2">
                                                <button class="btn btn-success btn-sm">✅ Setuju</button>
                                                <button class="btn btn-danger btn-sm">❌ Tolak</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nia Sari</strong><br><span class="text-sm text-muted">Guru</span></td>
                                        <td>Sound System JBL</td>
                                        <td>1 hari</td>
                                        <td>2025-06-10</td>
                                        <td>
                                            <div class="flex gap-2">
                                                <button class="btn btn-success btn-sm">✅ Setuju</button>
                                                <button class="btn btn-danger btn-sm">❌ Tolak</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Doni Pratama</strong><br><span class="text-sm text-muted">Panitia Event</span></td>
                                        <td>Kursi Plastik x50</td>
                                        <td>2 hari</td>
                                        <td>2025-06-12</td>
                                        <td>
                                            <div class="flex gap-2">
                                                <button class="btn btn-success btn-sm">✅ Setuju</button>
                                                <button class="btn btn-danger btn-sm">❌ Tolak</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php if ($role === 'admin' || $role === 'admin_fasilitas'): ?>
                <div id="tab-tambah-barang" class="tab-pane">
                    <div class="form-container">
                        <form method="POST" data-validate>
                            <div class="form-section">
                                <div class="form-section-title">📦 Informasi Barang</div>
                                <div class="field-group cols-2">
                                    <div class="form-field">
                                        <label>Nama Barang <span style="color:red">*</span></label>
                                        <input type="text" name="nama_barang" placeholder="Masukkan nama barang" required>
                                    </div>
                                    <div class="form-field">
                                        <label>Kode Barang</label>
                                        <input type="text" name="kode_barang" placeholder="Auto generate" readonly style="background:#f0f4f8;">
                                    </div>
                                </div>
                                <div class="field-group cols-3">
                                    <div class="form-field">
                                        <label>Kategori</label>
                                        <select name="kategori">
                                            <option>Elektronik</option>
                                            <option>Furnitur</option>
                                            <option>ATK</option>
                                            <option>Audio</option>
                                            <option>Kamera</option>
                                            <option>Mesin</option>
                                            <option>Lainnya</option>
                                        </select>
                                    </div>
                                    <div class="form-field">
                                        <label>Jumlah Stok <span style="color:red">*</span></label>
                                        <input type="number" name="stok" min="1" placeholder="0" required>
                                    </div>
                                    <div class="form-field">
                                        <label>Lokasi Penyimpanan</label>
                                        <input type="text" name="lokasi" placeholder="Contoh: Gudang IT">
                                    </div>
                                </div>
                                <div class="form-field">
                                    <label>Keterangan</label>
                                    <textarea name="keterangan" placeholder="Deskripsi kondisi dan catatan barang..."></textarea>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="reset" class="btn btn-outline">🔄 Reset</button>
                                <button type="submit" class="btn btn-success">💾 Simpan Barang</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- MODAL PINJAM -->
            <div class="modal-overlay" id="modalPinjam">
                <div class="modal">
                    <div class="modal-header">
                        <h3>📤 Form Peminjaman Barang</h3>
                        <button class="modal-close">✕</button>
                    </div>
                    <div class="modal-body">
                        <form data-validate>
                            <div class="form-field" style="margin-bottom:16px;">
                                <label>Nama Peminjam</label>
                                <input type="text" value="<?= htmlspecialchars($user['name']) ?>" readonly style="background:#f0f4f8;">
                            </div>
                            <div class="form-field" style="margin-bottom:16px;">
                                <label>Tanggal Pinjam</label>
                                <input type="date" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="form-field" style="margin-bottom:16px;">
                                <label>Tanggal Kembali <span style="color:red">*</span></label>
                                <input type="date" required>
                            </div>
                            <div class="form-field" style="margin-bottom:16px;">
                                <label>Jumlah yang Dipinjam</label>
                                <input type="number" min="1" value="1" required>
                            </div>
                            <div class="form-field">
                                <label>Keperluan</label>
                                <textarea placeholder="Jelaskan keperluan peminjaman..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-outline" data-close-modal>Batal</button>
                        <button class="btn btn-success" onclick="showAlert('Permintaan peminjaman berhasil diajukan!', 'success'); closeModal('modalPinjam');">📤 Ajukan Peminjaman</button>
                    </div>
                </div>
            </div>

            <?php
            break;

            case 'aktivitas':
            // ─────────────────────────────────────────
            // MODUL AKTIVITAS HARIAN
            // Hal.1 Use Case: Staff & Karyawan = Input Aktivitas Harian
            // ─────────────────────────────────────────
            ?>
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><?= $sub==='riwayat_aktivitas' ? '📋 Riwayat Aktivitas' : '✏️ Input Aktivitas Harian' ?></div>
                    <span class="badge badge-info">📅 <?= date('d F Y') ?></span>
                </div>
                <div class="card-body">
                <?php if ($sub === 'riwayat_aktivitas'): ?>
                    <table style="width:100%;border-collapse:collapse;font-size:14px;">
                        <thead><tr style="background:#f8fafc;">
                            <th style="padding:10px 14px;text-align:left;border-bottom:2px solid #d4e4da;">Tanggal</th>
                            <th style="padding:10px 14px;text-align:left;border-bottom:2px solid #d4e4da;">Jenis</th>
                            <th style="padding:10px 14px;text-align:left;border-bottom:2px solid #d4e4da;">Deskripsi</th>
                        </tr></thead>
                        <tbody>
                            <?php $akts=[['2025-06-07','Koordinasi','Rapat koordinasi kurikulum semester baru'],['2025-06-06','Administrasi','Upload dokumen RAB kegiatan'],['2025-06-05','Pengajaran','Monitoring absensi siswa kelas XII']];
                            foreach($akts as $a): ?>
                            <tr style="border-bottom:1px solid #f0f5f2;">
                                <td style="padding:11px 14px;">📅 <?= $a[0] ?></td>
                                <td style="padding:11px 14px;"><span class="badge badge-info"><?= $a[1] ?></span></td>
                                <td style="padding:11px 14px;"><?= $a[2] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <form data-validate style="max-width:560px;">
                        <div class="form-field" style="margin-bottom:14px;"><label>Tanggal</label><input type="date" value="<?= date('Y-m-d') ?>"></div>
                        <div class="form-field" style="margin-bottom:14px;"><label>Jenis Aktivitas</label>
                            <select><option>Administrasi</option><option>Pengajaran</option><option>Rapat</option><option>Koordinasi</option><option>Lainnya</option></select>
                        </div>
                        <div class="form-field" style="margin-bottom:16px;"><label>Deskripsi Aktivitas <span style="color:red">*</span></label>
                            <textarea placeholder="Deskripsikan kegiatan hari ini..." required style="min-height:90px;"></textarea>
                        </div>
                        <button type="button" class="btn btn-success" onclick="showAlert('Aktivitas harian berhasil dicatat!','success')">💾 Simpan Aktivitas</button>
                        <a href="dashboard.php?mod=aktivitas&sub=riwayat_aktivitas" class="btn btn-outline" style="margin-left:8px;">📋 Lihat Riwayat</a>
                    </form>
                <?php endif; ?>
                </div>
            </div>
            <?php break;

            case 'absensi':
            // ─────────────────────────────────────────
            // MODUL ABSENSI
            // Hal.4: Karyawan=Absensi Harian | AdminHR=Rekap Absensi, Cek Absensi
            // ─────────────────────────────────────────
            ?>
            <?php if (in_array($role,['admin','admin_hr'])): ?>
            <?php $absTab = $sub ?: 'rekap_absensi'; ?>
            <div style="display:flex;gap:8px;margin-bottom:18px;">
                <a href="dashboard.php?mod=absensi&sub=rekap_absensi" class="btn <?= $absTab==='rekap_absensi'?'btn-primary-sm':'btn-outline' ?>">📋 Rekap Absensi</a>
                <a href="dashboard.php?mod=absensi&sub=cek_absensi"  class="btn <?= $absTab==='cek_absensi'?'btn-primary-sm':'btn-outline' ?>">🔍 Cek Absensi</a>
            </div>
            <?php endif; ?>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">📋 <?= in_array($role,['admin','admin_hr']) ? ($sub==='cek_absensi'?'Cek Absensi':'Rekap Absensi') : 'Absensi Harian' ?> — <?= date('d F Y') ?></div>
                    <?php if ($role==='karyawan'): ?>
                    <button class="btn btn-success" onclick="showAlert('Absensi harian berhasil dicatat! ✅','success')">✅ Absen Sekarang</button>
                    <?php endif; ?>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Nama</th><th>Unit</th><th>Masuk</th><th>Keluar</th><th>Status</th><th>Keterangan</th></tr></thead>
                        <tbody>
                            <?php $abs=[['Budi Santoso','Tata Usaha','07:45','16:15','hadir',''],['Siti Rahma','Keuangan','08:02','16:00','hadir',''],['Ahmad Fauzi','Kurikulum','07:30','15:45','hadir',''],['Dewi Lestari','Kurikulum','','','izin','Sakit - Surat dokter'],['Rudi Hermawan','IT','08:15','16:30','hadir','']];
                            foreach($abs as $a): $sb=match($a[4]){'hadir'=>'<span class="badge badge-success">✅ Hadir</span>','izin'=>'<span class="badge badge-warning">🤒 Izin/Sakit</span>',default=>'<span class="badge badge-danger">❌ Absen</span>'}; ?>
                            <tr><td><strong><?= $a[0] ?></strong></td><td><?= $a[1] ?></td><td><?= $a[2]?:'<span class="text-muted">—</span>' ?></td><td><?= $a[3]?:'<span class="text-muted">—</span>' ?></td><td><?= $sb ?></td><td><span class="text-sm text-muted"><?= $a[5]?:'-' ?></span></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php break;

            case 'notifikasi':
            ?>
            <div class="card">
                <div class="card-header"><div class="card-title">🔔 Notifikasi</div><button class="btn btn-outline btn-sm" onclick="showAlert('Semua notifikasi ditandai sudah dibaca','success')">Tandai Semua Dibaca</button></div>
                <div class="card-body" style="padding:0;">
                    <?php $notifs=[['📦','3 peminjaman barang menunggu approval','5 menit lalu','unread'],['🔧','Maintenance AC Lab jatuh tempo besok','1 jam lalu','unread'],['🎪','Event Wisuda — 7 hari lagi','3 jam lalu','unread'],['📋','Rekap absensi bulan ini sudah tersedia','Kemarin','read'],['📁','Backup arsip berhasil','2 hari lalu','read']];
                    foreach($notifs as $n): ?>
                    <div style="padding:14px 22px;border-bottom:1px solid #f0f5f2;display:flex;gap:12px;align-items:flex-start;background:<?= $n[3]==='unread'?'#f0f9f4':'white' ?>;">
                        <div style="width:38px;height:38px;border-radius:50%;background:#eafaf1;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;"><?= $n[0] ?></div>
                        <div>
                            <p style="font-size:14px;line-height:1.4;"><?= $n[1] ?></p>
                            <span style="font-size:12px;color:#6b8070;"><?= $n[2] ?></span>
                            <?php if($n[3]==='unread'): ?><span style="margin-left:8px;background:#1a6b3c;color:white;font-size:10px;padding:2px 7px;border-radius:10px;">Baru</span><?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php break;

            case 'karyawan':
            ?>
            <!-- ===== KARYAWAN MODULE ===== -->
            <div class="stats-grid">
                <div class="stat-card c-blue">
                    <div class="stat-icon c-blue" style="border:none;">👥</div>
                    <div class="stat-info"><h3>87</h3><p>Total Karyawan</p></div>
                </div>
                <div class="stat-card c-green">
                    <div class="stat-icon c-green" style="border:none;">✅</div>
                    <div class="stat-info"><h3>74</h3><p>Hadir Hari Ini</p></div>
                </div>
                <div class="stat-card c-orange">
                    <div class="stat-icon c-orange" style="border:none;">🤒</div>
                    <div class="stat-info"><h3>8</h3><p>Sakit / Izin</p></div>
                </div>
                <div class="stat-card c-red">
                    <div class="stat-icon c-red" style="border:none;">❌</div>
                    <div class="stat-info"><h3>5</h3><p>Tidak Hadir</p></div>
                </div>
            </div>

            <div class="tab-group">
                <?php
                // Hal.4 HR/Karyawan Flowchart:
                //  Admin HR  → Kelola Karyawan | Rekap Absensi | Monitor Aktivitas | Monitor Kinerja
                //  Karyawan  → Lihat/Edit Profil
                $kTab = $sub ?: (in_array($role,['admin','admin_hr'])?'data_karyawan':'profil');
                ?>
                <div class="tab-nav">
                    <?php if (in_array($role,['admin','admin_hr'])): ?>
                    <button class="tab-btn <?= $kTab==='data_karyawan'?'active':'' ?>" onclick="location.href='dashboard.php?mod=karyawan&sub=data_karyawan'">👥 Kelola Data Karyawan</button>
                    <button class="tab-btn <?= $kTab==='rekap_absensi'?'active':'' ?>" onclick="location.href='dashboard.php?mod=karyawan&sub=rekap_absensi'">📋 Rekap Absensi</button>
                    <button class="tab-btn <?= $kTab==='monitoring_aktivitas'?'active':'' ?>" onclick="location.href='dashboard.php?mod=karyawan&sub=monitoring_aktivitas'">📝 Monitoring Aktivitas</button>
                    <button class="tab-btn <?= $kTab==='monitoring_kinerja'?'active':'' ?>" onclick="location.href='dashboard.php?mod=karyawan&sub=monitoring_kinerja'">📊 Monitoring Kinerja</button>
                    <?php elseif ($role === 'manager'): ?>
                    <button class="tab-btn <?= $kTab==='data_karyawan'?'active':'' ?>" onclick="location.href='dashboard.php?mod=karyawan&sub=data_karyawan'">👥 Data Karyawan</button>
                    <button class="tab-btn <?= $kTab==='monitoring_kinerja'?'active':'' ?>" onclick="location.href='dashboard.php?mod=karyawan&sub=monitoring_kinerja'">📊 Monitoring Kinerja</button>
                    <?php elseif ($role === 'karyawan'): ?>
                    <button class="tab-btn <?= $kTab==='profil'?'active':'' ?>" onclick="location.href='dashboard.php?mod=karyawan&sub=profil'">👤 Lihat / Edit Profil</button>
                    <?php endif; ?>
                </div>

                <div id="tab-data-karyawan" class="tab-pane active">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">👥 Daftar Karyawan</div>
                            <div class="flex gap-2">
                                <div class="search-input-wrap" style="width:220px;">
                                    <span class="search-icon">🔍</span>
                                    <input type="text" id="tableSearch" placeholder="Cari karyawan...">
                                </div>
                                <?php if ($role === 'admin' || $role === 'admin_hr'): ?>
                                <button class="btn btn-primary-sm" data-modal="modalTambahKaryawan">+ Tambah</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr><th>NIP</th><th>Nama</th><th>Jabatan</th><th>Unit</th><th>Status</th><th>Bergabung</th><th>Aksi</th></tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $karyawans = [
                                        ['KRY-001','Budi Santoso','Staff TU','Tata Usaha','aktif','2020-01-15'],
                                        ['KRY-002','Siti Rahma','Bendahara','Keuangan','aktif','2019-08-01'],
                                        ['KRY-003','Ahmad Fauzi','Guru Matematika','Kurikulum','aktif','2018-07-01'],
                                        ['KRY-004','Dewi Lestari','Guru Bahasa','Kurikulum','aktif','2021-01-03'],
                                        ['KRY-005','Rudi Hermawan','Teknisi IT','IT','aktif','2022-03-15'],
                                        ['KRY-006','Nia Sari','Koordinator Event','Event','cuti','2020-05-20'],
                                    ];
                                    foreach ($karyawans as $k):
                                        $sb = $k[4]==='aktif' ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-warning">Cuti</span>';
                                    ?>
                                    <tr>
                                        <td><code style="font-size:12px;background:#f0f4f8;padding:3px 7px;border-radius:5px;"><?= $k[0] ?></code></td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <div style="width:32px;height:32px;border-radius:50%;background:<?= $roleColor ?>;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:13px;flex-shrink:0;"><?= strtoupper($k[1][0]) ?></div>
                                                <strong><?= $k[1] ?></strong>
                                            </div>
                                        </td>
                                        <td><?= $k[2] ?></td>
                                        <td><?= $k[3] ?></td>
                                        <td><?= $sb ?></td>
                                        <td><?= $k[5] ?></td>
                                        <td>
                                            <div class="flex gap-2">
                                                <button class="btn btn-outline btn-sm">👁️ Detail</button>
                                                <?php if ($role === 'admin' || $role === 'admin_hr'): ?>
                                                <button class="btn btn-warning btn-sm">✏️</button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="tab-absensi-hari" class="tab-pane">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">📋 Absensi — <?= date('d F Y') ?></div>
                            <?php if ($role === 'karyawan'): ?>
                            <button class="btn btn-success" onclick="showAlert('Absensi harian berhasil dicatat!', 'success')">✅ Absen Sekarang</button>
                            <?php endif; ?>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr><th>Nama</th><th>Unit</th><th>Masuk</th><th>Keluar</th><th>Status</th><th>Keterangan</th></tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $absens = [
                                        ['Budi Santoso','Tata Usaha','07:45','16:15','hadir',''],
                                        ['Siti Rahma','Keuangan','08:02','16:00','hadir',''],
                                        ['Ahmad Fauzi','Kurikulum','07:30','15:45','hadir',''],
                                        ['Dewi Lestari','Kurikulum','','','izin','Sakit - Surat dokter'],
                                        ['Rudi Hermawan','IT','08:15','16:30','hadir',''],
                                        ['Nia Sari','Event','','','cuti','Cuti tahunan'],
                                    ];
                                    foreach ($absens as $a):
                                        $sb = match($a[4]) {
                                            'hadir' => '<span class="badge badge-success">✅ Hadir</span>',
                                            'izin' => '<span class="badge badge-warning">🤒 Izin/Sakit</span>',
                                            'cuti' => '<span class="badge badge-info">🌴 Cuti</span>',
                                            default => '<span class="badge badge-danger">❌ Absen</span>'
                                        };
                                    ?>
                                    <tr>
                                        <td><strong><?= $a[0] ?></strong></td>
                                        <td><?= $a[1] ?></td>
                                        <td><?= $a[2] ?: '<span class="text-muted">—</span>' ?></td>
                                        <td><?= $a[3] ?: '<span class="text-muted">—</span>' ?></td>
                                        <td><?= $sb ?></td>
                                        <td><span class="text-sm text-muted"><?= $a[5] ?: '-' ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="tab-aktivitas" class="tab-pane">
                    <div class="grid-2">
                        <div class="card">
                            <div class="card-header"><div class="card-title">📝 Input Aktivitas Harian</div></div>
                            <div class="card-body">
                                <form data-validate>
                                    <div class="form-field" style="margin-bottom:14px;">
                                        <label>Tanggal</label>
                                        <input type="date" value="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="form-field" style="margin-bottom:14px;">
                                        <label>Jenis Aktivitas</label>
                                        <select>
                                            <option>Administrasi</option>
                                            <option>Pengajaran</option>
                                            <option>Rapat</option>
                                            <option>Koordinasi</option>
                                            <option>Lainnya</option>
                                        </select>
                                    </div>
                                    <div class="form-field" style="margin-bottom:14px;">
                                        <label>Deskripsi Aktivitas <span style="color:red">*</span></label>
                                        <textarea placeholder="Deskripsikan kegiatan hari ini..." required style="min-height:80px;"></textarea>
                                    </div>
                                    <button type="button" class="btn btn-success w-full" onclick="showAlert('Aktivitas berhasil dicatat!', 'success')">💾 Simpan Aktivitas</button>
                                </form>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header"><div class="card-title">📋 Riwayat Aktivitas</div></div>
                            <div class="card-body" style="padding:0;">
                                <div class="activity-list" style="padding:0 22px;">
                                    <?php
                                    $acts = [
                                        ['Rapat koordinasi kurikulum semester baru','Rapat','2025-06-07','#eaf4fb','📅'],
                                        ['Upload dokumen RAB kegiatan','Administrasi','2025-06-06','#eafaf1','📄'],
                                        ['Monitoring absensi siswa kelas XII','Pengajaran','2025-06-05','#fef9e7','📊'],
                                        ['Koordinasi persiapan wisuda','Koordinasi','2025-06-04','#f4ecf7','🤝'],
                                    ];
                                    foreach ($acts as $a):
                                    ?>
                                    <div class="activity-item">
                                        <div class="activity-icon" style="background:<?= $a[3] ?>;"><?= $a[4] ?></div>
                                        <div class="activity-content">
                                            <p><strong><?= $a[0] ?></strong></p>
                                            <span><?= $a[1] ?> · <?= $a[2] ?></span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-kinerja" class="tab-pane">
                    <div class="card">
                        <div class="card-header"><div class="card-title">📊 Monitoring Kinerja Karyawan</div></div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr><th>Karyawan</th><th>Kehadiran</th><th>Aktivitas</th><th>Ketepatan</th><th>Skor</th><th>Grade</th></tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $kinerjas = [
                                        ['Budi Santoso',96,42,95,95,'A'],
                                        ['Siti Rahma',100,38,98,98,'A+'],
                                        ['Ahmad Fauzi',88,35,90,88,'B+'],
                                        ['Dewi Lestari',85,30,85,85,'B'],
                                        ['Rudi Hermawan',92,45,88,90,'A-'],
                                    ];
                                    foreach ($kinerjas as $kn):
                                        $gradeColor = $kn[5][0]==='A' ? 'badge-success' : ($kn[5][0]==='B' ? 'badge-info' : 'badge-warning');
                                    ?>
                                    <tr>
                                        <td><strong><?= $kn[0] ?></strong></td>
                                        <td>
                                            <?= $kn[1] ?>%
                                            <div class="progress-bar"><div class="progress-fill" style="width:<?= $kn[1] ?>%;background:#27ae60;"></div></div>
                                        </td>
                                        <td><?= $kn[2] ?> kegiatan</td>
                                        <td><?= $kn[3] ?>%</td>
                                        <td><strong><?= $kn[4] ?></strong></td>
                                        <td><span class="badge <?= $gradeColor ?>"><?= $kn[5] ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL TAMBAH KARYAWAN -->
            <div class="modal-overlay" id="modalTambahKaryawan">
                <div class="modal">
                    <div class="modal-header">
                        <h3>👥 Tambah Karyawan Baru</h3>
                        <button class="modal-close">✕</button>
                    </div>
                    <div class="modal-body">
                        <form data-validate>
                            <div class="field-group cols-2" style="margin-bottom:14px;">
                                <div class="form-field">
                                    <label>Nama Lengkap *</label>
                                    <input type="text" required>
                                </div>
                                <div class="form-field">
                                    <label>NIP</label>
                                    <input type="text" placeholder="Auto generate">
                                </div>
                            </div>
                            <div class="form-field" style="margin-bottom:14px;">
                                <label>Jabatan *</label>
                                <input type="text" required>
                            </div>
                            <div class="field-group cols-2" style="margin-bottom:14px;">
                                <div class="form-field">
                                    <label>Unit Kerja</label>
                                    <select><option>Tata Usaha</option><option>Keuangan</option><option>Kurikulum</option><option>IT</option><option>Event</option></select>
                                </div>
                                <div class="form-field">
                                    <label>Tanggal Bergabung</label>
                                    <input type="date">
                                </div>
                            </div>
                            <div class="form-field">
                                <label>Email</label>
                                <input type="email">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-outline" data-close-modal>Batal</button>
                        <button class="btn btn-success" onclick="showAlert('Karyawan berhasil ditambahkan!', 'success'); closeModal('modalTambahKaryawan');">💾 Simpan</button>
                    </div>
                </div>
            </div>

            <?php
            break;

            case 'event':
            ?>
            <!-- ===== EVENT MODULE ===== -->
            <div class="stats-grid">
                <div class="stat-card c-purple">
                    <div class="stat-icon c-purple" style="border:none;">🎪</div>
                    <div class="stat-info"><h3>12</h3><p>Total Event</p></div>
                </div>
                <div class="stat-card c-orange">
                    <div class="stat-icon c-orange" style="border:none;">⏳</div>
                    <div class="stat-info"><h3>3</h3><p>Persiapan</p></div>
                </div>
                <div class="stat-card c-blue">
                    <div class="stat-icon c-blue" style="border:none;">🔄</div>
                    <div class="stat-info"><h3>1</h3><p>Sedang Berlangsung</p></div>
                </div>
                <div class="stat-card c-green">
                    <div class="stat-icon c-green" style="border:none;">✅</div>
                    <div class="stat-info"><h3>8</h3><p>Selesai</p></div>
                </div>
            </div>

            <div class="tab-group">
                <?php
                // Hal.7 Event Flowchart:
                //  EO     → Buat Event | Kelola/Edit | Assign | Update Status | Laporan
                //  Admin  → Approval Event | Monitoring Event | Daftar Event
                //  Staff  → Lihat Event Saya | Lihat Reminder
                $evTab = $sub ?: ($role==='eo'?'buat_event':($role==='staff'?'event_saya':($role==='manager'?'evaluasi_event':'approval_event')));
                ?>
                <div class="tab-nav">
                    <?php if ($role === 'admin'): ?>
                    <button class="tab-btn <?= $evTab==='approval_event'?'active':'' ?>" onclick="location.href='dashboard.php?mod=event&sub=approval_event'">✅ Approval Event</button>
                    <button class="tab-btn <?= $evTab==='monitoring_event'?'active':'' ?>" onclick="location.href='dashboard.php?mod=event&sub=monitoring_event'">📡 Monitoring Event</button>
                    <button class="tab-btn <?= $evTab==='daftar_event'?'active':'' ?>" onclick="location.href='dashboard.php?mod=event&sub=daftar_event'">📋 Daftar Event</button>
                    <?php elseif ($role === 'eo'): ?>
                    <button class="tab-btn <?= $evTab==='buat_event'?'active':'' ?>" onclick="location.href='dashboard.php?mod=event&sub=buat_event'">➕ Buat Event</button>
                    <button class="tab-btn <?= $evTab==='kelola_event'?'active':'' ?>" onclick="location.href='dashboard.php?mod=event&sub=kelola_event'">✏️ Kelola / Edit Event</button>
                    <button class="tab-btn <?= $evTab==='assign'?'active':'' ?>" onclick="location.href='dashboard.php?mod=event&sub=assign'">👥 Assign Personel &amp; Fasilitas</button>
                    <button class="tab-btn <?= $evTab==='update_status'?'active':'' ?>" onclick="location.href='dashboard.php?mod=event&sub=update_status'">🔄 Update Status Event</button>
                    <button class="tab-btn <?= $evTab==='laporan_event'?'active':'' ?>" onclick="location.href='dashboard.php?mod=laporan&sub=laporan_event'">📊 Lihat Laporan</button>
                    <?php elseif ($role === 'manager'): ?>
                    <button class="tab-btn <?= $evTab==='daftar_event'?'active':'' ?>" onclick="location.href='dashboard.php?mod=event&sub=daftar_event'">📋 Daftar Event</button>
                    <button class="tab-btn <?= $evTab==='evaluasi_event'?'active':'' ?>" onclick="location.href='dashboard.php?mod=event&sub=evaluasi_event'">🏆 Evaluasi Event</button>
                    <button class="tab-btn <?= $evTab==='laporan_event'?'active':'' ?>" onclick="location.href='dashboard.php?mod=laporan&sub=laporan_event'">📊 Laporan Event</button>
                    <?php elseif ($role === 'staff'): ?>
                    <button class="tab-btn <?= $evTab==='event_saya'?'active':'' ?>" onclick="location.href='dashboard.php?mod=event&sub=event_saya'">🎫 Lihat Event Saya</button>
                    <button class="tab-btn <?= $evTab==='reminder'?'active':'' ?>" onclick="location.href='dashboard.php?mod=event&sub=reminder'">🔔 Lihat Reminder</button>
                    <?php else: ?>
                    <button class="tab-btn active">📋 Daftar Event</button>
                    <?php endif; ?>
                </div>

                <div id="tab-event-list" class="tab-pane active">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">🎪 Semua Event</div>
                            <?php if ($role === 'eo' || $role === 'admin'): ?>
                            <button class="btn btn-primary-sm" data-modal="modalBuatEvent">+ Event Baru</button>
                            <?php endif; ?>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr><th>Nama Event</th><th>Tanggal</th><th>Lokasi</th><th>Personel</th><th>Status</th><th>Aksi</th></tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $events = [
                                        ['Wisuda Angkatan 2025','2025-06-15','Aula Utama',25,'persiapan'],
                                        ['Pesantren Kilat Ramadan','2025-07-01','Masjid Al-Syukro',15,'persiapan'],
                                        ['Lomba Kreativitas Siswa','2025-06-20','Lapangan Sekolah',20,'persiapan'],
                                        ['Rapat Kerja Tahunan','2025-06-10','Ruang Rapat',30,'berlangsung'],
                                        ['Seminar Pendidikan 2025','2025-05-20','Aula Utama',18,'selesai'],
                                        ['Peringatan HUT Sekolah','2025-04-15','Seluruh Sekolah',50,'selesai'],
                                    ];
                                    foreach ($events as $ev):
                                        $sb = match($ev[4]) {
                                            'persiapan' => '<span class="badge badge-warning">⏳ Persiapan</span>',
                                            'berlangsung' => '<span class="badge badge-primary">🔄 Berlangsung</span>',
                                            'selesai' => '<span class="badge badge-success">✅ Selesai</span>',
                                            default => '<span class="badge badge-secondary">-</span>'
                                        };
                                    ?>
                                    <tr>
                                        <td><strong><?= $ev[0] ?></strong></td>
                                        <td>📅 <?= date('d M Y', strtotime($ev[1])) ?></td>
                                        <td>📍 <?= $ev[2] ?></td>
                                        <td>👥 <?= $ev[3] ?> orang</td>
                                        <td><?= $sb ?></td>
                                        <td>
                                            <div class="flex gap-2">
                                                <button class="btn btn-outline btn-sm">👁️ Detail</button>
                                                <?php if (($role === 'eo' || $role === 'admin') && $ev[4] !== 'selesai'): ?>
                                                <button class="btn btn-info btn-sm">✏️ Update</button>
                                                <?php endif; ?>
                                                <?php if ($ev[4] === 'selesai'): ?>
                                                <button class="btn btn-warning btn-sm">📊 Evaluasi</button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php if ($role === 'eo' || $role === 'admin'): ?>
                <div id="tab-buat-event" class="tab-pane">
                    <div class="form-container">
                        <form data-validate>
                            <div class="form-section">
                                <div class="form-section-title">🎪 Detail Event</div>
                                <div class="field-group cols-2">
                                    <div class="form-field">
                                        <label>Nama Event *</label>
                                        <input type="text" placeholder="Nama kegiatan" required>
                                    </div>
                                    <div class="form-field">
                                        <label>Jenis Event</label>
                                        <select><option>Akademik</option><option>Non-Akademik</option><option>Internal</option><option>Eksternal</option></select>
                                    </div>
                                </div>
                                <div class="field-group cols-3">
                                    <div class="form-field">
                                        <label>Tanggal Mulai *</label>
                                        <input type="date" required>
                                    </div>
                                    <div class="form-field">
                                        <label>Tanggal Selesai</label>
                                        <input type="date">
                                    </div>
                                    <div class="form-field">
                                        <label>Lokasi</label>
                                        <input type="text" placeholder="Tempat event">
                                    </div>
                                </div>
                                <div class="form-field">
                                    <label>Deskripsi Event</label>
                                    <textarea placeholder="Jelaskan detail kegiatan, tujuan, dan target..."></textarea>
                                </div>
                            </div>
                            <div class="form-section">
                                <div class="form-section-title">👥 Kebutuhan Personel & Fasilitas</div>
                                <div class="field-group cols-2">
                                    <div class="form-field">
                                        <label>Jumlah Personel</label>
                                        <input type="number" min="1">
                                    </div>
                                    <div class="form-field">
                                        <label>Fasilitas yang Dibutuhkan</label>
                                        <input type="text" placeholder="Proyektor, sound, dll">
                                    </div>
                                </div>
                                <div class="form-field">
                                    <label>Catatan Khusus</label>
                                    <textarea placeholder="Hal-hal yang perlu diperhatikan..."></textarea>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="reset" class="btn btn-outline">🔄 Reset</button>
                                <button type="button" class="btn btn-success" onclick="showAlert('Event berhasil dibuat dan menunggu approval!', 'success')">🎪 Buat Event</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <div id="tab-jadwal-event" class="tab-pane">
                    <div class="card">
                        <div class="card-header"><div class="card-title">📅 Jadwal Event Juni 2025</div></div>
                        <div class="card-body">
                            <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:8px;margin-bottom:8px;">
                                <?php foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $d): ?>
                                <div style="text-align:center;font-size:12px;font-weight:700;color:#7f8c8d;padding:6px;"><?= $d ?></div>
                                <?php endforeach; ?>
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:8px;">
                                <?php
                                $events_dates = [10,15,20];
                                for ($i = 1; $i <= 30; $i++):
                                    $hasEvent = in_array($i, $events_dates);
                                    $style = $hasEvent ? 'background:#3498db;color:white;' : 'background:#f8fafc;color:#2c3e50;';
                                    if ($i === (int)date('d')) $style = 'background:#e74c3c;color:white;';
                                ?>
                                <div style="<?= $style ?>border-radius:8px;padding:8px 4px;text-align:center;font-size:14px;font-weight:600;cursor:pointer;transition:0.2s;" title="<?= $hasEvent ? 'Ada event' : '' ?>">
                                    <?= $i ?>
                                    <?php if ($hasEvent): ?><div style="width:4px;height:4px;background:white;border-radius:50%;margin:2px auto 0;"></div><?php endif; ?>
                                </div>
                                <?php endfor; ?>
                            </div>
                            <div style="margin-top:16px;display:flex;gap:12px;font-size:13px;">
                                <span><span style="display:inline-block;width:12px;height:12px;background:#e74c3c;border-radius:50%;margin-right:4px;"></span>Hari Ini</span>
                                <span><span style="display:inline-block;width:12px;height:12px;background:#3498db;border-radius:50%;margin-right:4px;"></span>Ada Event</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-laporan-event" class="tab-pane">
                    <div class="card">
                        <div class="card-header"><div class="card-title">📊 Evaluasi & Laporan Event</div></div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr><th>Event</th><th>Peserta</th><th>Skor Evaluasi</th><th>Rating</th><th>Laporan</th></tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Seminar Pendidikan 2025</strong></td>
                                        <td>312 orang</td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <div class="progress-bar" style="width:100px;">
                                                    <div class="progress-fill" style="width:88%;background:#27ae60;"></div>
                                                </div>
                                                88%
                                            </div>
                                        </td>
                                        <td>⭐⭐⭐⭐⭐ 4.8</td>
                                        <td><button class="btn btn-info btn-sm">📄 Lihat</button></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Peringatan HUT Sekolah</strong></td>
                                        <td>850 orang</td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <div class="progress-bar" style="width:100px;">
                                                    <div class="progress-fill" style="width:92%;background:#27ae60;"></div>
                                                </div>
                                                92%
                                            </div>
                                        </td>
                                        <td>⭐⭐⭐⭐⭐ 4.9</td>
                                        <td><button class="btn btn-info btn-sm">📄 Lihat</button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL BUAT EVENT -->
            <div class="modal-overlay" id="modalBuatEvent">
                <div class="modal">
                    <div class="modal-header"><h3>🎪 Event Baru</h3><button class="modal-close">✕</button></div>
                    <div class="modal-body">
                        <div class="form-field" style="margin-bottom:14px;"><label>Nama Event *</label><input type="text" required></div>
                        <div class="field-group cols-2" style="margin-bottom:14px;">
                            <div class="form-field"><label>Tanggal *</label><input type="date" required></div>
                            <div class="form-field"><label>Lokasi</label><input type="text"></div>
                        </div>
                        <div class="form-field"><label>Deskripsi</label><textarea></textarea></div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-outline" data-close-modal>Batal</button>
                        <button class="btn btn-success" onclick="showAlert('Event berhasil dibuat!', 'success'); closeModal('modalBuatEvent');">💾 Simpan</button>
                    </div>
                </div>
            </div>

            <?php
            break;

            case 'maintenance':
            ?>
            <!-- ===== MAINTENANCE MODULE ===== -->
            <div class="stats-grid">
                <div class="stat-card c-orange">
                    <div class="stat-icon c-orange" style="border:none;">🔧</div>
                    <div class="stat-info"><h3>34</h3><p>Total Fasilitas</p></div>
                </div>
                <div class="stat-card c-green">
                    <div class="stat-icon c-green" style="border:none;">✅</div>
                    <div class="stat-info"><h3>28</h3><p>Kondisi Baik</p></div>
                </div>
                <div class="stat-card c-red">
                    <div class="stat-icon c-red" style="border:none;">⚠️</div>
                    <div class="stat-info"><h3>4</h3><p>Perlu Perbaikan</p></div>
                </div>
                <div class="stat-card c-blue">
                    <div class="stat-icon c-blue" style="border:none;">🔄</div>
                    <div class="stat-info"><h3>2</h3><p>Sedang Maintenance</p></div>
                </div>
            </div>

            <div class="tab-group">
                <?php
                // Hal.3 Maintenance Flowchart:
                //  Admin/AdminFas → Kelola Fasilitas | Kelola Jadwal | Laporan
                //  Teknisi        → Lihat Tugas | Jadwal | Proses/Update Status
                //  Staff/Karyawan → Ajukan Request | Lacak Request
                $mTab = $sub ?: (in_array($role,['admin','admin_fasilitas'])?'kelola_fasilitas':($role==='teknisi'?'tugas_maint':'request_maint'));
                ?>
                <div class="tab-nav">
                    <?php if (in_array($role,['admin','admin_fasilitas'])): ?>
                    <button class="tab-btn <?= $mTab==='kelola_fasilitas'?'active':'' ?>" onclick="location.href='dashboard.php?mod=maintenance&sub=kelola_fasilitas'">🏗️ Kelola Data Fasilitas</button>
                    <button class="tab-btn <?= $mTab==='jadwal_maint'?'active':'' ?>" onclick="location.href='dashboard.php?mod=maintenance&sub=jadwal_maint'">📅 Kelola Jadwal Maintenance</button>
                    <button class="tab-btn <?= $mTab==='laporan_maint'?'active':'' ?>" onclick="location.href='dashboard.php?mod=maintenance&sub=laporan_maint'">📊 Laporan Maintenance</button>
                    <?php elseif ($role === 'teknisi'): ?>
                    <button class="tab-btn <?= $mTab==='tugas_maint'?'active':'' ?>" onclick="location.href='dashboard.php?mod=maintenance&sub=tugas_maint'">🗒️ Lihat Tugas</button>
                    <button class="tab-btn <?= $mTab==='jadwal_maint'?'active':'' ?>" onclick="location.href='dashboard.php?mod=maintenance&sub=jadwal_maint'">📅 Jadwal Maintenance</button>
                    <button class="tab-btn <?= $mTab==='proses_maint'?'active':'' ?>" onclick="location.href='dashboard.php?mod=maintenance&sub=proses_maint'">🔧 Proses / Update Status</button>
                    <?php elseif ($role === 'manager'): ?>
                    <button class="tab-btn <?= $mTab==='laporan_maint'?'active':'' ?>" onclick="location.href='dashboard.php?mod=maintenance&sub=laporan_maint'">📊 Laporan Maintenance</button>
                    <button class="tab-btn <?= $mTab==='jadwal_maint'?'active':'' ?>" onclick="location.href='dashboard.php?mod=maintenance&sub=jadwal_maint'">📅 Jadwal Maintenance</button>
                    <?php else: /* staff / karyawan */ ?>
                    <button class="tab-btn <?= $mTab==='request_maint'?'active':'' ?>" onclick="location.href='dashboard.php?mod=maintenance&sub=request_maint'">🛠️ Ajukan Request Maintenance</button>
                    <button class="tab-btn <?= $mTab==='lacak_request'?'active':'' ?>" onclick="location.href='dashboard.php?mod=maintenance&sub=lacak_request'">🔍 Lacak Request</button>
                    <?php endif; ?>
                </div>

                <div id="tab-fasilitas" class="tab-pane active">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">🏗️ Data Fasilitas Sekolah</div>
                            <?php if ($role === 'admin_fasilitas' || $role === 'admin'): ?>
                            <button class="btn btn-primary-sm" data-modal="modalTambahFasilitas">+ Tambah</button>
                            <?php endif; ?>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr><th>Kode</th><th>Nama Fasilitas</th><th>Lokasi</th><th>Kondisi</th><th>Maint. Terakhir</th><th>Berikutnya</th><th>Aksi</th></tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $fasilitas = [
                                        ['FAS-001','AC Ruang Guru','Ruang Guru','baik','2025-05-01','2025-08-01'],
                                        ['FAS-002','AC Lab Komputer','Lab IPA','perbaikan','2025-04-15','2025-06-15'],
                                        ['FAS-003','Genset Cadangan','Gudang','baik','2025-05-15','2025-07-15'],
                                        ['FAS-004','CCTV Koridor','Seluruh Gedung','maintenance','2025-03-01','2025-06-01'],
                                        ['FAS-005','Lift Gedung B','Gedung B','baik','2025-04-01','2025-07-01'],
                                        ['FAS-006','Pompa Air','Ruang Pompa','perbaikan','2025-02-01','2025-05-01'],
                                    ];
                                    foreach ($fasilitas as $f):
                                        $kondisiBadge = match($f[3]) {
                                            'baik' => '<span class="badge badge-success">✅ Baik</span>',
                                            'perbaikan' => '<span class="badge badge-danger">⚠️ Perlu Perbaikan</span>',
                                            'maintenance' => '<span class="badge badge-warning">🔧 Maintenance</span>',
                                            default => '<span class="badge badge-secondary">-</span>'
                                        };
                                        $isOverdue = strtotime($f[5]) < time() && $f[3] === 'baik';
                                    ?>
                                    <tr>
                                        <td><code style="font-size:12px;background:#f0f4f8;padding:3px 7px;border-radius:5px;"><?= $f[0] ?></code></td>
                                        <td><strong><?= $f[1] ?></strong></td>
                                        <td>📍 <?= $f[2] ?></td>
                                        <td><?= $kondisiBadge ?></td>
                                        <td><?= $f[4] ?></td>
                                        <td style="color:<?= $isOverdue ? '#e74c3c' : 'inherit' ?>">
                                            <?= $f[5] ?><?= $isOverdue ? ' ⚠️' : '' ?>
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <?php if ($role === 'teknisi' || $role === 'admin_fasilitas' || $role === 'admin'): ?>
                                                <button class="btn btn-warning btn-sm">🔧 Update</button>
                                                <?php endif; ?>
                                                <button class="btn btn-outline btn-sm">👁️</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="tab-jadwal-maint" class="tab-pane">
                    <div class="card">
                        <div class="card-header"><div class="card-title">📅 Jadwal Maintenance</div></div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr><th>Fasilitas</th><th>Jenis</th><th>Jadwal</th><th>Teknisi</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $jadwal = [
                                        ['AC Lab Komputer','Rutin','2025-06-10','Rudi Hermawan','terjadwal'],
                                        ['CCTV Koridor','Perbaikan','2025-06-08','Tim Teknik','dalam proses'],
                                        ['Pompa Air','Darurat','2025-06-07','Teknisi Luar','dalam proses'],
                                        ['Genset','Rutin','2025-07-15','Rudi Hermawan','terjadwal'],
                                    ];
                                    foreach ($jadwal as $j):
                                        $sb = match($j[4]) {
                                            'terjadwal' => '<span class="badge badge-info">📅 Terjadwal</span>',
                                            'dalam proses' => '<span class="badge badge-warning">🔧 Proses</span>',
                                            'selesai' => '<span class="badge badge-success">✅ Selesai</span>',
                                            default => '<span class="badge">-</span>'
                                        };
                                    ?>
                                    <tr>
                                        <td><strong><?= $j[0] ?></strong></td>
                                        <td><?= $j[1] ?></td>
                                        <td><?= $j[2] ?></td>
                                        <td>🔧 <?= $j[3] ?></td>
                                        <td><?= $sb ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="tab-request" class="tab-pane">
                    <div class="grid-2">
                        <div class="form-container">
                            <div class="form-section-title" style="margin-bottom:18px;">📝 Ajukan Request Maintenance</div>
                            <form data-validate>
                                <div class="form-field" style="margin-bottom:14px;">
                                    <label>Fasilitas Bermasalah *</label>
                                    <select required>
                                        <option value="">-- Pilih Fasilitas --</option>
                                        <option>AC Ruang Guru</option>
                                        <option>AC Lab Komputer</option>
                                        <option>Genset</option>
                                        <option>CCTV</option>
                                        <option>Lainnya</option>
                                    </select>
                                </div>
                                <div class="form-field" style="margin-bottom:14px;">
                                    <label>Prioritas</label>
                                    <select>
                                        <option>Rendah</option>
                                        <option>Normal</option>
                                        <option selected>Tinggi</option>
                                        <option>Darurat</option>
                                    </select>
                                </div>
                                <div class="form-field" style="margin-bottom:14px;">
                                    <label>Deskripsi Masalah *</label>
                                    <textarea placeholder="Jelaskan detail masalah yang ditemukan..." required></textarea>
                                </div>
                                <button type="button" class="btn btn-warning w-full" onclick="showAlert('Request maintenance berhasil diajukan!', 'success')">📤 Ajukan Request</button>
                            </form>
                        </div>
                        <div class="card">
                            <div class="card-header"><div class="card-title">📋 Request Masuk</div></div>
                            <div class="card-body" style="padding:0;">
                                <?php
                                $requests = [
                                    ['Budi Santoso','AC bocor di ruang kepala','Tinggi','menunggu','#fff3cd'],
                                    ['Dewi Lestari','Lampu aula mati','Normal','diproses','#d1ecf1'],
                                    ['Ahmad Fauzi','Kran air rusak','Rendah','selesai','#d4edda'],
                                ];
                                foreach ($requests as $r):
                                ?>
                                <div style="padding:14px 22px;border-bottom:1px solid #f0f4f8;">
                                    <div class="flex justify-between items-center">
                                        <strong><?= $r[1] ?></strong>
                                        <span class="badge" style="background:<?= $r[3]==='menunggu'?'#fff3cd':($r[3]==='diproses'?'#d1ecf1':'#d4edda') ?>;color:#333;"><?= $r[3] ?></span>
                                    </div>
                                    <div style="font-size:12px;color:#7f8c8d;margin-top:4px;">Oleh: <?= $r[0] ?> · Prioritas: <?= $r[2] ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-laporan-maint" class="tab-pane">
                    <div class="card">
                        <div class="card-header"><div class="card-title">📊 Laporan Maintenance</div></div>
                        <div class="card-body">
                            <div class="grid-3" style="margin-bottom:24px;">
                                <div style="text-align:center;padding:20px;background:#eafaf1;border-radius:10px;">
                                    <div style="font-size:36px;font-weight:800;color:#27ae60;">18</div>
                                    <div style="font-size:13px;color:#7f8c8d;">Selesai Bulan Ini</div>
                                </div>
                                <div style="text-align:center;padding:20px;background:#fef9e7;border-radius:10px;">
                                    <div style="font-size:36px;font-weight:800;color:#f39c12;">4</div>
                                    <div style="font-size:13px;color:#7f8c8d;">Dalam Proses</div>
                                </div>
                                <div style="text-align:center;padding:20px;background:#fdedec;border-radius:10px;">
                                    <div style="font-size:36px;font-weight:800;color:#e74c3c;">2</div>
                                    <div style="font-size:13px;color:#7f8c8d;">Overdue</div>
                                </div>
                            </div>
                            <div class="chart-placeholder">
                                <div class="chart-icon">📊</div>
                                <p>Grafik trend maintenance — 6 bulan terakhir</p>
                                <span style="font-size:12px;color:#bdc3c7;">Jan: 12 | Feb: 8 | Mar: 15 | Apr: 10 | Mei: 18 | Jun: 4</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            break;

            case 'arsip':
            ?>
            <!-- ===== ARSIP MODULE ===== -->
            <div class="stats-grid">
                <div class="stat-card c-blue">
                    <div class="stat-icon c-blue" style="border:none;">📁</div>
                    <div class="stat-info"><h3>1,248</h3><p>Total Dokumen</p></div>
                </div>
                <div class="stat-card c-green">
                    <div class="stat-icon c-green" style="border:none;">📂</div>
                    <div class="stat-info"><h3>24</h3><p>Kategori Arsip</p></div>
                </div>
                <div class="stat-card c-teal">
                    <div class="stat-icon c-teal" style="border:none;">☁️</div>
                    <div class="stat-info"><h3>42 GB</h3><p>Penyimpanan Digital</p></div>
                </div>
                <div class="stat-card c-orange">
                    <div class="stat-icon c-orange" style="border:none;">🔄</div>
                    <div class="stat-info"><h3>3 hari</h3><p>Backup Terakhir</p></div>
                </div>
            </div>

            <div class="tab-group">
                <?php
                // Hal.2 Arsip Digital Flowchart:
                //  Admin/Admin Arsip → Kelola Arsip | Klasifikasi | Pencarian | Backup & Restore
                //  Manager           → Lihat Arsip | Laporan Arsip
                //  Staff/Karyawan    → Cari Arsip | Upload Dokumen | Riwayat Upload
                $aTab = $sub ?: (in_array($role,['admin','admin_arsip'])?'kelola_arsip':($role==='manager'?'lihat_arsip':'cari_arsip'));
                ?>
                <div class="tab-nav">
                    <?php if (in_array($role,['admin','admin_arsip'])): ?>
                    <button class="tab-btn <?= $aTab==='kelola_arsip'?'active':'' ?>" onclick="location.href='dashboard.php?mod=arsip&sub=kelola_arsip'">📁 Kelola Arsip Digital</button>
                    <button class="tab-btn <?= $aTab==='klasifikasi'?'active':'' ?>" onclick="location.href='dashboard.php?mod=arsip&sub=klasifikasi'">🏷️ Kelola Klasifikasi Arsip</button>
                    <button class="tab-btn <?= $aTab==='pencarian_arsip'?'active':'' ?>" onclick="location.href='dashboard.php?mod=arsip&sub=pencarian_arsip'">🔍 Pencarian Arsip</button>
                    <button class="tab-btn <?= $aTab==='backup_restore'?'active':'' ?>" onclick="location.href='dashboard.php?mod=arsip&sub=backup_restore'">💾 Backup &amp; Restore</button>
                    <?php elseif ($role === 'manager'): ?>
                    <button class="tab-btn <?= $aTab==='lihat_arsip'?'active':'' ?>" onclick="location.href='dashboard.php?mod=arsip&sub=lihat_arsip'">📂 Lihat Arsip</button>
                    <button class="tab-btn <?= $aTab==='laporan_arsip'?'active':'' ?>" onclick="location.href='dashboard.php?mod=arsip&sub=laporan_arsip'">📊 Laporan Arsip</button>
                    <?php else: /* staff / karyawan */ ?>
                    <button class="tab-btn <?= $aTab==='cari_arsip'?'active':'' ?>" onclick="location.href='dashboard.php?mod=arsip&sub=cari_arsip'">🔍 Cari Arsip</button>
                    <button class="tab-btn <?= $aTab==='upload_dok'?'active':'' ?>" onclick="location.href='dashboard.php?mod=arsip&sub=upload_dok'">⬆️ Upload Dokumen</button>
                    <button class="tab-btn <?= $aTab==='riwayat_arsip'?'active':'' ?>" onclick="location.href='dashboard.php?mod=arsip&sub=riwayat_arsip'">📋 Riwayat Upload</button>
                    <?php endif; ?>
                    <?php if (false): // placeholder for backup legacy ?>
                    <button class="tab-btn" data-tab="tab-backup">💾 Backup &amp; Restore</button>
                    <?php endif; ?>
                </div>

                <div id="tab-arsip-list" class="tab-pane active">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">📁 Daftar Arsip Digital</div>
                            <div class="flex gap-2">
                                <div class="search-input-wrap" style="width:220px;">
                                    <span class="search-icon">🔍</span>
                                    <input type="text" id="tableSearch" placeholder="Cari arsip...">
                                </div>
                            </div>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr><th>Kode</th><th>Nama Dokumen</th><th>Kategori</th><th>Tahun</th><th>Ukuran</th><th>Aksi</th></tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $arsips = [
                                        ['ARS-001','SK Pengangkatan Guru 2024','SDM','2024','2.4 MB','pdf'],
                                        ['ARS-002','Laporan Keuangan Q1 2025','Keuangan','2025','1.8 MB','xlsx'],
                                        ['ARS-003','Foto Wisuda Angkatan 2024','Dokumentasi','2024','450 MB','zip'],
                                        ['ARS-004','Kurikulum Tahun Ajaran 2024','Akademik','2024','3.2 MB','pdf'],
                                        ['ARS-005','Notulen Rapat Kerja 2025','Administrasi','2025','0.8 MB','docx'],
                                        ['ARS-006','Data Inventaris 2024','Sarana','2024','1.2 MB','xlsx'],
                                    ];
                                    $icons = ['pdf' => '📄', 'xlsx' => '📊', 'zip' => '🗜️', 'docx' => '📝'];
                                    foreach ($arsips as $a):
                                    ?>
                                    <tr>
                                        <td><code style="font-size:12px;background:#f0f4f8;padding:3px 7px;border-radius:5px;"><?= $a[0] ?></code></td>
                                        <td>
                                            <span style="margin-right:6px;"><?= $icons[$a[5]] ?? '📁' ?></span>
                                            <strong><?= $a[1] ?></strong>
                                        </td>
                                        <td><span class="badge badge-info"><?= $a[2] ?></span></td>
                                        <td><?= $a[3] ?></td>
                                        <td><?= $a[4] ?></td>
                                        <td>
                                            <div class="flex gap-2">
                                                <button class="btn btn-info btn-sm">👁️ Lihat</button>
                                                <button class="btn btn-success btn-sm">⬇️ Unduh</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="tab-upload" class="tab-pane">
                    <div class="form-container">
                        <div class="form-section-title" style="margin-bottom:18px;">⬆️ Upload Dokumen Baru</div>
                        <form data-validate>
                            <div class="field-group cols-2" style="margin-bottom:16px;">
                                <div class="form-field">
                                    <label>Nama Dokumen *</label>
                                    <input type="text" placeholder="Judul dokumen" required>
                                </div>
                                <div class="form-field">
                                    <label>Kategori Arsip *</label>
                                    <select required>
                                        <option value="">-- Pilih Kategori --</option>
                                        <option>SDM</option><option>Keuangan</option><option>Akademik</option>
                                        <option>Administrasi</option><option>Dokumentasi</option><option>Sarana</option>
                                    </select>
                                </div>
                            </div>
                            <div class="field-group cols-3" style="margin-bottom:16px;">
                                <div class="form-field">
                                    <label>Tahun Dokumen</label>
                                    <input type="number" value="<?= date('Y') ?>">
                                </div>
                                <div class="form-field">
                                    <label>Tingkat Kerahasiaan</label>
                                    <select>
                                        <option>Publik</option>
                                        <option>Internal</option>
                                        <option>Rahasia</option>
                                    </select>
                                </div>
                                <div class="form-field">
                                    <label>Kode Arsip</label>
                                    <input type="text" placeholder="Auto generate" readonly style="background:#f0f4f8;">
                                </div>
                            </div>
                            <div class="form-field" style="margin-bottom:16px;">
                                <label>File Dokumen *</label>
                                <div style="border:2px dashed #dce6f0;border-radius:10px;padding:30px;text-align:center;background:#f8fafc;cursor:pointer;">
                                    <div style="font-size:36px;margin-bottom:8px;">📁</div>
                                    <p style="font-size:14px;color:#7f8c8d;">Drag & drop file di sini atau <span style="color:#2980b9;cursor:pointer;">klik untuk memilih</span></p>
                                    <p style="font-size:12px;color:#bdc3c7;margin-top:4px;">PDF, DOCX, XLSX, ZIP — Maks. 50MB</p>
                                    <input type="file" style="display:none;" required>
                                </div>
                            </div>
                            <div class="form-field" style="margin-bottom:16px;">
                                <label>Keterangan</label>
                                <textarea placeholder="Deskripsi singkat dokumen..."></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="reset" class="btn btn-outline">🔄 Reset</button>
                                <button type="button" class="btn btn-success" onclick="showAlert('Dokumen berhasil diupload!', 'success')">⬆️ Upload Dokumen</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="tab-klasifikasi" class="tab-pane">
                    <div class="module-grid">
                        <?php
                        $kats = [
                            ['📂','SDM','Sumber Daya Manusia','234 dokumen','#eaf4fb'],
                            ['💰','Keuangan','Laporan & Data Keuangan','187 dokumen','#eafaf1'],
                            ['🎓','Akademik','Kurikulum & Data Siswa','312 dokumen','#f4ecf7'],
                            ['📋','Administrasi','Surat & Notulen','156 dokumen','#fef9e7'],
                            ['📸','Dokumentasi','Foto & Video Kegiatan','89 dokumen','#fdedec'],
                            ['🏗️','Sarana','Inventaris & Fasilitas','98 dokumen','#f0f4f8'],
                        ];
                        foreach ($kats as $k):
                        ?>
                        <div class="module-card" style="border-top-color:<?= $k[4] ?>;">
                            <span class="mod-icon"><?= $k[0] ?></span>
                            <h3><?= $k[1] ?></h3>
                            <p><?= $k[2] ?></p>
                            <div class="mod-stats">
                                <div class="mod-stat"><strong><?= $k[3] ?></strong></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($role === 'admin_arsip' || $role === 'admin'): ?>
                <div id="tab-backup" class="tab-pane">
                    <div class="grid-2">
                        <div class="card">
                            <div class="card-header"><div class="card-title">💾 Backup & Restore</div></div>
                            <div class="card-body">
                                <div style="background:#eafaf1;border-radius:10px;padding:18px;margin-bottom:16px;">
                                    <div style="font-size:13px;color:#7f8c8d;">Last Backup</div>
                                    <div style="font-size:18px;font-weight:700;color:#27ae60;">3 hari lalu — 03 Juni 2025</div>
                                    <div style="font-size:13px;color:#7f8c8d;margin-top:4px;">Ukuran: 2.4 GB · Status: Berhasil ✅</div>
                                </div>
                                <button class="btn btn-success w-full" style="margin-bottom:10px;" onclick="showAlert('Backup dimulai, proses mungkin memakan waktu beberapa menit.', 'info')">💾 Backup Sekarang</button>
                                <button class="btn btn-warning w-full">🔄 Restore dari Backup</button>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header"><div class="card-title">📋 Riwayat Backup</div></div>
                            <div class="card-body" style="padding:0;">
                                <?php
                                $bkps = [
                                    ['03 Jun 2025','2.4 GB','Berhasil','#d4edda'],
                                    ['27 Mei 2025','2.3 GB','Berhasil','#d4edda'],
                                    ['20 Mei 2025','2.1 GB','Berhasil','#d4edda'],
                                    ['13 Mei 2025','2.0 GB','Gagal','#fdecea'],
                                    ['06 Mei 2025','1.9 GB','Berhasil','#d4edda'],
                                ];
                                foreach ($bkps as $b):
                                ?>
                                <div style="padding:12px 22px;border-bottom:1px solid #f0f4f8;display:flex;justify-content:space-between;align-items:center;">
                                    <div>
                                        <strong style="font-size:14px;"><?= $b[0] ?></strong>
                                        <div style="font-size:12px;color:#7f8c8d;"><?= $b[1] ?></div>
                                    </div>
                                    <span class="badge" style="background:<?= $b[3] ?>;color:#333;"><?= $b[2] ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php
            break;

            case 'laporan':
            ?>
            <!-- ===== LAPORAN MODULE ===== -->
            <div class="stats-grid">
                <div class="stat-card c-purple">
                    <div class="stat-icon c-purple" style="border:none;">📊</div>
                    <div class="stat-info"><h3>48</h3><p>Laporan Dibuat</p></div>
                </div>
                <div class="stat-card c-blue">
                    <div class="stat-icon c-blue" style="border:none;">📈</div>
                    <div class="stat-info"><h3>87%</h3><p>Efisiensi Operasional</p></div>
                </div>
                <div class="stat-card c-green">
                    <div class="stat-icon c-green" style="border:none;">✅</div>
                    <div class="stat-info"><h3>96%</h3><p>Target Terpenuhi</p></div>
                </div>
                <div class="stat-card c-orange">
                    <div class="stat-icon c-orange" style="border:none;">⏱️</div>
                    <div class="stat-info"><h3>2.3s</h3><p>Avg. Response Time</p></div>
                </div>
            </div>

            <div class="grid-2">
                <div class="card">
                    <div class="card-header"><div class="card-title">📊 Ringkasan Modul</div></div>
                    <div class="card-body">
                        <?php
                        $summaries = [
                            ['📦 Inventaris','248 barang, 47 dipinjam','#2980b9',89],
                            ['👥 Karyawan','87 aktif, 85% kehadiran','#27ae60',85],
                            ['🎪 Event','12 event, 8 selesai','#9b59b6',75],
                            ['🔧 Maintenance','34 fasilitas, 82% kondisi baik','#e67e22',82],
                        ];
                        foreach ($summaries as $s):
                        ?>
                        <div style="margin-bottom:20px;">
                            <div class="flex justify-between" style="margin-bottom:6px;">
                                <span style="font-size:14px;font-weight:600;"><?= $s[0] ?></span>
                                <span style="font-size:12px;color:#7f8c8d;"><?= $s[1] ?></span>
                            </div>
                            <div class="progress-bar" style="height:10px;">
                                <div class="progress-fill" style="width:<?= $s[3] ?>%;background:<?= $s[2] ?>;"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><div class="card-title">📈 Analitik Bulanan</div></div>
                    <div class="card-body">
                        <div class="chart-placeholder">
                            <div class="chart-icon">📈</div>
                            <p>Grafik Aktivitas Operasional 6 Bulan</p>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:16px;">
                            <div style="text-align:center;padding:14px;background:#eaf4fb;border-radius:8px;">
                                <div style="font-size:24px;font-weight:800;color:#2980b9;">+12%</div>
                                <div style="font-size:12px;color:#7f8c8d;">Efisiensi Naik</div>
                            </div>
                            <div style="text-align:center;padding:14px;background:#eafaf1;border-radius:8px;">
                                <div style="font-size:24px;font-weight:800;color:#27ae60;">-8%</div>
                                <div style="font-size:12px;color:#7f8c8d;">Biaya Turun</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top:24px;">
                <div class="card-header">
                    <div class="card-title">📋 Generate Laporan</div>
                </div>
                <div class="card-body">
                    <div class="module-grid">
                        <?php
                        $reports = [
                            ['📦','Laporan Inventaris','Rekap stok, peminjaman, dan pengembalian','#eaf4fb'],
                            ['👥','Laporan SDM','Kehadiran, kinerja, dan aktivitas karyawan','#eafaf1'],
                            ['🎪','Laporan Event','Evaluasi kegiatan dan dokumentasi event','#f4ecf7'],
                            ['🔧','Laporan Maintenance','Status fasilitas dan rekap perbaikan','#fef9e7'],
                            ['📁','Laporan Arsip','Statistik dokumen dan aktivitas pengarsipan','#fdedec'],
                            ['📊','Laporan Komprehensif','Ringkasan seluruh modul operasional','#f0f4f8'],
                        ];
                        foreach ($reports as $r):
                        ?>
                        <div style="background:<?= $r[3] ?>;border-radius:10px;padding:20px;cursor:pointer;transition:0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform=''">
                            <div style="font-size:30px;margin-bottom:10px;"><?= $r[0] ?></div>
                            <h4 style="font-size:14px;font-weight:700;margin-bottom:4px;"><?= $r[1] ?></h4>
                            <p style="font-size:12px;color:#7f8c8d;margin-bottom:14px;"><?= $r[2] ?></p>
                            <div class="flex gap-2">
                                <button class="btn btn-outline btn-sm" onclick="showAlert('Preview laporan dibuka!', 'info')">👁️ Preview</button>
                                <button class="btn btn-primary-sm btn-sm" onclick="showAlert('Laporan berhasil diunduh!', 'success')">⬇️ Unduh</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <?php
            break;

            case 'users':
            if ($role !== 'admin') { echo '<div class="alert alert-error">❌ Akses ditolak.</div>'; break; }
            ?>
            <!-- ===== USERS MODULE ===== -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">🔑 Kelola Pengguna Sistem</div>
                    <button class="btn btn-primary-sm" data-modal="modalTambahUser">+ Tambah User</button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Username</th><th>Nama</th><th>Email</th><th>Role</th><th>Unit</th><th>Bergabung</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $allUsers = getUsers();
                            foreach ($allUsers as $u):
                                $roleColor2 = getRoleColor($u['role']);
                            ?>
                            <tr>
                                <td><code style="font-size:12px;background:#f0f4f8;padding:3px 7px;border-radius:5px;">@<?= htmlspecialchars($u['username']) ?></code></td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div style="width:32px;height:32px;border-radius:50%;background:<?= $roleColor2 ?>;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:13px;flex-shrink:0;"><?= strtoupper($u['name'][0]) ?></div>
                                        <strong><?= htmlspecialchars($u['name']) ?></strong>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><span class="badge badge-primary" style="background:<?= $roleColor2 ?>22;color:<?= $roleColor2 ?>;"><?= getRoleName($u['role']) ?></span></td>
                                <td><?= htmlspecialchars($u['unit'] ?? '-') ?></td>
                                <td><?= $u['created_at'] ?? '-' ?></td>
                                <td>
                                    <div class="flex gap-2">
                                        <button class="btn btn-outline btn-sm">✏️ Edit</button>
                                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <button class="btn btn-danger btn-sm" data-confirm="Hapus user ini?">🗑️</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-overlay" id="modalTambahUser">
                <div class="modal">
                    <div class="modal-header"><h3>➕ Tambah User Baru</h3><button class="modal-close">✕</button></div>
                    <div class="modal-body">
                        <div class="field-group cols-2" style="margin-bottom:14px;">
                            <div class="form-field"><label>Nama Lengkap *</label><input type="text" required></div>
                            <div class="form-field"><label>Username *</label><input type="text" required></div>
                        </div>
                        <div class="form-field" style="margin-bottom:14px;"><label>Email *</label><input type="email" required></div>
                        <div class="field-group cols-2" style="margin-bottom:14px;">
                            <div class="form-field">
                                <label>Role</label>
                                <select>
                                    <option value="staff">Staff</option>
                                    <option value="karyawan">Karyawan</option>
                                    <option value="admin_hr">Admin HR</option>
                                    <option value="eo">Event Organizer</option>
                                    <option value="admin_fasilitas">Admin Fasilitas</option>
                                    <option value="teknisi">Teknisi</option>
                                    <option value="admin_arsip">Admin Arsip</option>
                                    <option value="manager">Manager</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>
                            <div class="form-field"><label>Unit Kerja</label><input type="text"></div>
                        </div>
                        <div class="form-field"><label>Password Awal *</label><input type="password" required></div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-outline" data-close-modal>Batal</button>
                        <button class="btn btn-success" onclick="showAlert('User berhasil ditambahkan!', 'success'); closeModal('modalTambahUser');">💾 Simpan</button>
                    </div>
                </div>
            </div>

            <?php
            break;

            case 'profile':
            ?>
            <!-- ===== PROFILE PAGE ===== -->
            <div class="profile-header">
                <div class="profile-avatar-lg" style="background:<?= $roleColor ?>;"><?= $userInitial ?></div>
                <div class="profile-info">
                    <h2><?= htmlspecialchars($user['name']) ?></h2>
                    <p>@<?= htmlspecialchars($user['username']) ?> · <?= htmlspecialchars($user['email']) ?></p>
                    <div class="badges">
                        <span class="role-badge"><?= $roleName ?></span>
                        <span class="role-badge">🏢 <?= htmlspecialchars($user['unit'] ?? 'Umum') ?></span>
                        <span class="role-badge">📅 Bergabung <?= $user['created_at'] ?? date('Y-m-d') ?></span>
                    </div>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-container">
                    <div class="form-section-title" style="margin-bottom:18px;">👤 Edit Profil</div>
                    <form data-validate>
                        <div class="form-field" style="margin-bottom:14px;">
                            <label>Nama Lengkap</label>
                            <input type="text" value="<?= htmlspecialchars($user['name']) ?>">
                        </div>
                        <div class="form-field" style="margin-bottom:14px;">
                            <label>Email</label>
                            <input type="email" value="<?= htmlspecialchars($user['email']) ?>">
                        </div>
                        <div class="form-field" style="margin-bottom:14px;">
                            <label>Unit Kerja</label>
                            <input type="text" value="<?= htmlspecialchars($user['unit'] ?? '') ?>">
                        </div>
                        <div class="form-field" style="margin-bottom:16px;">
                            <label>Role / Jabatan</label>
                            <input type="text" value="<?= $roleName ?>" readonly style="background:#f0f4f8;">
                        </div>
                        <button type="button" class="btn btn-success w-full" onclick="showAlert('Profil berhasil diperbarui!', 'success')">💾 Simpan Perubahan</button>
                    </form>
                </div>

                <div class="form-container">
                    <div class="form-section-title" style="margin-bottom:18px;">🔐 Ubah Password</div>
                    <form data-validate>
                        <div class="form-field" style="margin-bottom:14px;">
                            <label>Password Lama *</label>
                            <div class="input-wrap"><span class="input-icon">🔒</span><input type="password" required></div>
                        </div>
                        <div class="form-field" style="margin-bottom:14px;">
                            <label>Password Baru *</label>
                            <div class="input-wrap"><span class="input-icon">🔑</span><input type="password" required></div>
                        </div>
                        <div class="form-field" style="margin-bottom:16px;">
                            <label>Konfirmasi Password *</label>
                            <div class="input-wrap"><span class="input-icon">🔐</span><input type="password" required></div>
                        </div>
                        <button type="button" class="btn btn-warning w-full" onclick="showAlert('Password berhasil diubah!', 'success')">🔑 Ubah Password</button>
                    </form>

                    <div style="margin-top:24px;padding:16px;background:#f8fafc;border-radius:8px;">
                        <div style="font-size:13px;font-weight:700;margin-bottom:12px;color:#2c3e50;">🛡️ Akses Modul</div>
                        <?php foreach ($modules as $mod):
                            $modNames = ['inventory'=>'📦 Inventaris','karyawan'=>'👥 Karyawan','absensi'=>'📋 Absensi','event'=>'🎪 Event','maintenance'=>'🔧 Maintenance','arsip'=>'📁 Arsip','laporan'=>'📊 Laporan'];
                        ?>
                        <span class="badge badge-success" style="margin:3px;"><?= $modNames[$mod] ?? $mod ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <?php
            break;

            default:
            // ===== DASHBOARD HOME =====
            ?>
            <!-- ===== MAIN DASHBOARD ===== -->
            <div style="background:linear-gradient(135deg,#0f4525,#2d9b5a);border-radius:14px;padding:24px 28px;color:white;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div>
                    <h2 style="font-size:22px;font-weight:700;margin-bottom:4px;">
                        Selamat datang, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?> 👋
                    </h2>
                    <p style="opacity:0.85;font-size:14px;">
                        Login sebagai <strong><?= $roleName ?></strong> · <?= htmlspecialchars($user['unit'] ?? 'Umum') ?> · <span id="currentDateInline"></span>
                    </p>
                    <p style="opacity:0.7;font-size:12px;margin-top:4px;">
                        <?php
                        $roleDesc = [
                            'admin'           => 'Akses penuh ke seluruh modul sistem',
                            'staff'           => 'Pinjam barang · Input aktivitas · Ajukan request · Upload arsip',
                            'karyawan'        => 'Absensi harian · Input aktivitas · Request maintenance',
                            'admin_hr'        => 'Kelola karyawan · Rekap absensi · Monitoring aktivitas & kinerja',
                            'eo'              => 'Buat event · Kelola event · Assign personel & fasilitas',
                            'admin_fasilitas' => 'Kelola fasilitas · Jadwal maintenance · Laporan maintenance',
                            'teknisi'         => 'Lihat tugas · Jadwal maintenance · Proses & update status',
                            'manager'         => 'Dashboard analitik · Approval peminjaman · Evaluasi event · Semua laporan',
                            'admin_arsip'     => 'Kelola arsip · Klasifikasi · Pencarian arsip · Backup & restore',
                        ];
                        echo $roleDesc[$role] ?? '';
                        ?>
                    </p>
                </div>
                <img src="logo.png" alt="Logo" style="height:52px;opacity:0.9;filter:brightness(10);mix-blend-mode:screen;">
            </div>
            <script>document.getElementById('currentDateInline') && (document.getElementById('currentDateInline').textContent = new Date().toLocaleDateString('id-ID',{weekday:'long',year:'numeric',month:'long',day:'numeric'}));</script>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card c-blue">
                    <div class="stat-icon c-blue" style="border:none;">📦</div>
                    <div class="stat-info">
                        <h3 class="stat-number" data-target="248">248</h3>
                        <p>Total Barang</p>
                        <div class="stat-change up">▲ 12 bulan ini</div>
                    </div>
                </div>
                <div class="stat-card c-green">
                    <div class="stat-icon c-green" style="border:none;">👥</div>
                    <div class="stat-info">
                        <h3 class="stat-number" data-target="87">87</h3>
                        <p>Total Karyawan</p>
                        <div class="stat-change up">▲ 74 hadir hari ini</div>
                    </div>
                </div>
                <div class="stat-card c-purple">
                    <div class="stat-icon c-purple" style="border:none;">🎪</div>
                    <div class="stat-info">
                        <h3 class="stat-number" data-target="12">12</h3>
                        <p>Event Aktif</p>
                        <div class="stat-change up">3 dalam persiapan</div>
                    </div>
                </div>
                <div class="stat-card c-orange">
                    <div class="stat-icon c-orange" style="border:none;">🔧</div>
                    <div class="stat-info">
                        <h3 class="stat-number" data-target="34">34</h3>
                        <p>Total Fasilitas</p>
                        <div class="stat-change down">⚠️ 4 perlu perbaikan</div>
                    </div>
                </div>
            </div>

            <!-- Module Cards -->
            <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;color:var(--primary-dark);">📌 Modul yang Dapat Diakses</h3>
            <div class="module-grid" style="margin-bottom:28px;">
                <?php
                $allModules = [
                    'inventory' => ['📦','Inventaris & Peminjaman','Kelola aset sekolah, proses peminjaman, dan approval',['248 barang','47 dipinjam'],'#eaf4fb','#2980b9'],
                    'karyawan' => ['👥','Manajemen Karyawan','Data pegawai, absensi, aktivitas, dan monitoring kinerja',['87 aktif','85% hadir'],'#eafaf1','#27ae60'],
                    'event' => ['🎪','Event Management','Rencanakan dan kelola event dari persiapan hingga evaluasi',['12 event','3 persiapan'],'#f4ecf7','#9b59b6'],
                    'maintenance' => ['🔧','Maintenance Fasilitas','Pantau kondisi dan jadwal perawatan fasilitas sekolah',['34 fasilitas','4 perbaikan'],'#fef9e7','#e67e22'],
                    'arsip' => ['📁','Arsip Digital','Simpan dan kelola dokumen operasional secara digital',['1248 dok','42 GB'],'#f0f4f8','#7f8c8d'],
                    'laporan' => ['📊','Laporan & Analitik','Dashboard analitik dan generate laporan operasional',['48 laporan','87% efisiensi'],'#eaf4fb','#2980b9'],
                ];
                foreach ($modules as $mod):
                    if (!isset($allModules[$mod])) continue;
                    $m = $allModules[$mod];
                ?>
                <a href="dashboard.php?mod=<?= $mod ?>" class="module-card" style="border-top-color:<?= $m[5] ?>;">
                    <span class="mod-icon"><?= $m[0] ?></span>
                    <h3><?= $m[1] ?></h3>
                    <p><?= $m[2] ?></p>
                    <div class="mod-stats">
                        <div class="mod-stat"><strong><?= $m[3][0] ?></strong></div>
                        <div class="mod-stat"><strong><?= $m[3][1] ?></strong></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Recent Activity & Quick Stats -->
            <div class="grid-2">
                <div class="card">
                    <div class="card-header"><div class="card-title">🔔 Aktivitas Terbaru</div></div>
                    <div class="card-body" style="padding:0;">
                        <div class="activity-list" style="padding:0 22px;">
                            <div class="activity-item">
                                <div class="activity-icon" style="background:#eaf4fb;">📦</div>
                                <div class="activity-content">
                                    <p><strong>3 permintaan peminjaman</strong> menunggu persetujuan</p>
                                    <span>5 menit lalu</span>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon" style="background:#fdedec;">⚠️</div>
                                <div class="activity-content">
                                    <p><strong>Maintenance AC Lab</strong> jatuh tempo besok</p>
                                    <span>1 jam lalu</span>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon" style="background:#f4ecf7;">🎪</div>
                                <div class="activity-content">
                                    <p>Event <strong>Wisuda 2025</strong> — 7 hari lagi</p>
                                    <span>3 jam lalu</span>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon" style="background:#eafaf1;">✅</div>
                                <div class="activity-content">
                                    <p>Absensi harian <strong>74 karyawan</strong> telah tercatat</p>
                                    <span>Pagi ini</span>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon" style="background:#f0f4f8;">📁</div>
                                <div class="activity-content">
                                    <p><strong>5 dokumen baru</strong> berhasil diarsipkan</p>
                                    <span>Kemarin</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><div class="card-title">📋 Perlu Perhatian</div></div>
                    <div class="card-body" style="padding:0;">
                        <?php
                        $alerts = [
                            ['🔴','3 peminjaman barang belum dikembalikan','Inventaris','urgent'],
                            ['🟡','AC Lab Komputer jadwal maintenance besok','Fasilitas','warning'],
                            ['🟠','5 karyawan belum input aktivitas harian','SDM','warning'],
                            ['🔵','Event Wisuda membutuhkan konfirmasi personel','Event','info'],
                            ['⚪','Backup arsip sudah 3 hari tidak dilakukan','Arsip','normal'],
                        ];
                        foreach ($alerts as $a):
                        ?>
                        <div style="padding:13px 22px;border-bottom:1px solid #f0f4f8;display:flex;gap:12px;align-items:flex-start;">
                            <span style="font-size:18px;margin-top:1px;"><?= $a[0] ?></span>
                            <div>
                                <p style="font-size:13.5px;line-height:1.4;"><?= $a[1] ?></p>
                                <span style="font-size:11px;color:#7f8c8d;">📌 <?= $a[2] ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <?php
            break;
            } // end switch
            ?>
        </main>
    </div>
</div>

<style>
@media(max-width:900px){
    #menuToggle { display:flex !important; }
    #sidebarOverlay { display:block !important; opacity:0; visibility:hidden; transition:0.25s; }
    .sidebar.open ~ * #sidebarOverlay { opacity:1; visibility:visible; }
}
</style>

<script src="js/main.js"></script>
<script>
// Mobile menu toggle
const menuBtn = document.getElementById('menuToggle');
const sidebar = document.querySelector('.sidebar');
const sOverlay = document.getElementById('sidebarOverlay');
if(menuBtn){
    menuBtn.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        if(sidebar.classList.contains('open')){
            sOverlay.style.cssText = 'display:block;opacity:1;visibility:visible;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99;';
        } else {
            sOverlay.style.cssText = 'display:none;';
        }
    });
}
if(sOverlay){
    sOverlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        sOverlay.style.cssText = 'display:none;';
    });
}
</script>
</body>
</html>
