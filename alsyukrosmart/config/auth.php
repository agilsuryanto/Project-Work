<?php
session_start();

// Demo users data (in production, use a real database)
$USERS_FILE = __DIR__ . '/users.json';

function getUsers() {
    global $USERS_FILE;
    if (!file_exists($USERS_FILE)) {
        $default = [
            [
                'id' => 1, 'name' => 'Administrator', 'username' => 'admin',
                'email' => 'admin@alsyukro.sch.id', 'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin', 'unit' => 'IT', 'created_at' => date('Y-m-d')
            ],
            [
                'id' => 2, 'name' => 'Budi Santoso', 'username' => 'staff1',
                'email' => 'budi@alsyukro.sch.id', 'password' => password_hash('staff123', PASSWORD_DEFAULT),
                'role' => 'staff', 'unit' => 'Umum', 'created_at' => date('Y-m-d')
            ],
            [
                'id' => 3, 'name' => 'Siti Rahma', 'username' => 'karyawan1',
                'email' => 'siti@alsyukro.sch.id', 'password' => password_hash('karya123', PASSWORD_DEFAULT),
                'role' => 'karyawan', 'unit' => 'Keuangan', 'created_at' => date('Y-m-d')
            ],
            [
                'id' => 4, 'name' => 'HR Manager', 'username' => 'adminhr',
                'email' => 'hr@alsyukro.sch.id', 'password' => password_hash('hr123456', PASSWORD_DEFAULT),
                'role' => 'admin_hr', 'unit' => 'HR', 'created_at' => date('Y-m-d')
            ],
            [
                'id' => 5, 'name' => 'Event Organizer', 'username' => 'eo',
                'email' => 'eo@alsyukro.sch.id', 'password' => password_hash('eo123456', PASSWORD_DEFAULT),
                'role' => 'eo', 'unit' => 'Event', 'created_at' => date('Y-m-d')
            ],
            [
                'id' => 6, 'name' => 'Admin Fasilitas', 'username' => 'adminfas',
                'email' => 'fasilitas@alsyukro.sch.id', 'password' => password_hash('fas12345', PASSWORD_DEFAULT),
                'role' => 'admin_fasilitas', 'unit' => 'Fasilitas', 'created_at' => date('Y-m-d')
            ],
            [
                'id' => 7, 'name' => 'Teknisi Utama', 'username' => 'teknisi',
                'email' => 'teknisi@alsyukro.sch.id', 'password' => password_hash('teks1234', PASSWORD_DEFAULT),
                'role' => 'teknisi', 'unit' => 'Teknik', 'created_at' => date('Y-m-d')
            ],
            [
                'id' => 8, 'name' => 'Kepala Sekolah', 'username' => 'manager',
                'email' => 'kepala@alsyukro.sch.id', 'password' => password_hash('mgr12345', PASSWORD_DEFAULT),
                'role' => 'manager', 'unit' => 'Pimpinan', 'created_at' => date('Y-m-d')
            ],
            [
                'id' => 9, 'name' => 'Admin Arsip', 'username' => 'adminarsip',
                'email' => 'arsip@alsyukro.sch.id', 'password' => password_hash('arsip123', PASSWORD_DEFAULT),
                'role' => 'admin_arsip', 'unit' => 'Arsip', 'created_at' => date('Y-m-d')
            ]
        ];
        file_put_contents($USERS_FILE, json_encode($default, JSON_PRETTY_PRINT));
        return $default;
    }
    return json_decode(file_get_contents($USERS_FILE), true);
}

function saveUsers($users) {
    global $USERS_FILE;
    file_put_contents($USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $users = getUsers();
    foreach ($users as $user) {
        if ($user['id'] == $_SESSION['user_id']) return $user;
    }
    return null;
}

function getRoleName($role) {
    $roles = [
        'admin' => 'Administrator',
        'staff' => 'Staff',
        'karyawan' => 'Karyawan',
        'admin_hr' => 'Admin HR',
        'eo' => 'Event Organizer',
        'admin_fasilitas' => 'Admin Fasilitas',
        'teknisi' => 'Teknisi',
        'manager' => 'Manager/Pimpinan',
        'admin_arsip' => 'Admin Arsip'
    ];
    return $roles[$role] ?? $role;
}

function getRoleColor($role) {
    $colors = [
        'admin' => '#e74c3c',
        'staff' => '#3498db',
        'karyawan' => '#2ecc71',
        'admin_hr' => '#9b59b6',
        'eo' => '#f39c12',
        'admin_fasilitas' => '#1abc9c',
        'teknisi' => '#e67e22',
        'manager' => '#c0392b',
        'admin_arsip' => '#7f8c8d'
    ];
    return $colors[$role] ?? '#95a5a6';
}

/**
 * Mendefinisikan modul utama yang bisa diakses setiap role.
 * Berdasarkan Use Case Diagram (hal.1) + Flowchart per modul (hal.2-8).
 *
 * Diagram hal.8  → Inventory : Admin=kelola+approval, Staff=pinjam+kembalikan
 * Diagram hal.4  → HR/Karyawan: AdminHR=kelola+rekap, Karyawan=profil+absensi+aktivitas
 * Diagram hal.7  → Event: EO=buat+kelola, Admin=approval+monitor, Staff=lihat+reminder
 * Diagram hal.3  → Maintenance: AdminFas=kelola+jadwal, Teknisi=tugas+status, Staff/Karyawan=request
 * Diagram hal.2  → Arsip: AdminArsip=kelola+backup, Manager=dashboard+lihat, Staff=cari+upload
 */
function getRoleModules($role) {
    $modules = [
        // Admin sistem – akses penuh ke semua modul
        'admin' => ['inventory', 'karyawan', 'event', 'maintenance', 'arsip', 'laporan'],

        // Staff – inventory (pinjam/kembali), aktivitas harian, event (lihat), maintenance (request), arsip (cari/upload)
        'staff' => ['inventory', 'aktivitas', 'event', 'maintenance', 'arsip'],

        // Karyawan – absensi harian, aktivitas, profil, request maintenance, upload dokumen
        'karyawan' => ['absensi', 'aktivitas', 'maintenance'],

        // Admin HR – kelola data karyawan, rekap absensi, monitoring aktivitas & kinerja
        'admin_hr' => ['karyawan', 'absensi', 'laporan'],

        // Event Organizer – buat/kelola event, assign personel & fasilitas, laporan event
        'eo' => ['event', 'laporan'],

        // Admin Fasilitas – kelola data fasilitas, jadwal maintenance, laporan maintenance
        'admin_fasilitas' => ['maintenance', 'laporan'],

        // Teknisi – lihat tugas maintenance, update/proses status maintenance
        'teknisi' => ['maintenance'],

        // Manager/Pimpinan – dashboard analitik, approval peminjaman, evaluasi event, semua laporan, lihat arsip
        'manager' => ['laporan', 'inventory', 'event', 'maintenance', 'arsip'],

        // Admin Arsip – kelola arsip digital, klasifikasi, backup & restore, pencarian arsip
        'admin_arsip' => ['arsip', 'laporan'],
    ];
    return $modules[$role] ?? [];
}

/**
 * Mendefinisikan sub-menu spesifik setiap role di dalam setiap modul.
 * Ini yang menentukan TAB APA yang muncul di dalam halaman modul.
 *
 * Format: [tab_id => label]
 */
function getRoleSubMenus($role) {
    return [
        // ───── MODUL INVENTORY ─────
        // Hal.8: Admin=Kelola Data Barang, Lihat Laporan, Persetujuan Peminjaman
        //        Staff=Pinjam Barang, Kembalikan Barang
        // Hal.1 Use Case: juga Pencatatan & Pengembalian
        'inventory' => match($role) {
            'admin'           => ['barang'=>'Kelola Data Barang','approval'=>'Persetujuan Peminjaman','laporan_inv'=>'Lihat Laporan'],
            'manager'         => ['approval'=>'Approval Peminjaman','laporan_inv'=>'Laporan Inventaris'],
            'admin_fasilitas' => ['barang'=>'Data Barang','riwayat'=>'Riwayat Peminjaman'],
            'staff'           => ['pinjam'=>'Pinjam Barang','kembali'=>'Kembalikan Barang','riwayat'=>'Riwayat Peminjaman'],
            default           => ['riwayat'=>'Lihat Riwayat Peminjaman'],
        },

        // ───── MODUL KARYAWAN & HR ─────
        // Hal.4: AdminHR=Kelola Karyawan, Rekap Absensi, Monitoring Aktivitas, Monitoring Kinerja
        //        Karyawan=Lihat/Edit Profil, Absensi Harian, Aktivitas Harian, Lihat Notifikasi
        'karyawan' => match($role) {
            'admin','admin_hr' => ['data_karyawan'=>'Kelola Data Karyawan','rekap_absensi'=>'Rekap Absensi','monitoring_aktivitas'=>'Monitoring Aktivitas','monitoring_kinerja'=>'Monitoring Kinerja'],
            'manager'          => ['data_karyawan'=>'Data Karyawan','monitoring_kinerja'=>'Monitoring Kinerja'],
            default            => ['profil'=>'Lihat/Edit Profil'],
        },

        // ───── MODUL ABSENSI ─────
        // Hal.4: Karyawan=Absensi Harian | AdminHR=Rekap Absensi, Cek Absensi
        'absensi' => match($role) {
            'admin','admin_hr' => ['rekap_absensi'=>'Rekap Absensi','cek_absensi'=>'Cek Absensi'],
            'karyawan'         => ['absensi_harian'=>'Absensi Harian'],
            default            => ['absensi_harian'=>'Absensi Harian'],
        },

        // ───── MODUL AKTIVITAS ─────
        // Hal.1 Use Case: Staff & Karyawan=Input Aktivitas Harian
        'aktivitas' => match($role) {
            'admin','admin_hr' => ['monitoring_aktivitas'=>'Monitoring Aktivitas'],
            default            => ['input_aktivitas'=>'Input Aktivitas Harian','riwayat_aktivitas'=>'Riwayat Aktivitas'],
        },

        // ───── MODUL EVENT ─────
        // Hal.7: EO=Buat Event, Kelola/Edit Event, Lihat Laporan
        //        Admin=Approval Event, Monitoring Event
        //        Staff=Lihat Event Saya, Lihat Reminder
        // Hal.1 Use Case: EO juga Update Status Event, Assign Personel & Fasilitas
        'event' => match($role) {
            'admin'   => ['approval_event'=>'Approval Event','monitoring_event'=>'Monitoring Event','daftar_event'=>'Daftar Event'],
            'eo'      => ['buat_event'=>'Buat Event','kelola_event'=>'Kelola / Edit Event','assign'=>'Assign Personel & Fasilitas','update_status'=>'Update Status Event','laporan_event'=>'Lihat Laporan'],
            'manager' => ['daftar_event'=>'Daftar Event','evaluasi_event'=>'Evaluasi Event','laporan_event'=>'Laporan Event'],
            'staff'   => ['event_saya'=>'Lihat Event Saya','reminder'=>'Lihat Reminder'],
            default   => ['daftar_event'=>'Daftar Event'],
        },

        // ───── MODUL MAINTENANCE ─────
        // Hal.3: AdminFas=Kelola Jadwal, Laporan, Kelola Fasilitas
        //        Teknisi=Lihat Tugas, Update Status
        //        Staff=Ajukan Request, Lacak Request
        // Hal.1 Use Case: Karyawan=Request Maintenance
        'maintenance' => match($role) {
            'admin','admin_fasilitas' => ['kelola_fasilitas'=>'Kelola Data Fasilitas','jadwal_maint'=>'Kelola Jadwal Maintenance','laporan_maint'=>'Laporan Maintenance'],
            'teknisi'                 => ['tugas_maint'=>'Lihat Tugas','proses_maint'=>'Proses / Update Status'],
            'manager'                 => ['laporan_maint'=>'Laporan Maintenance','jadwal_maint'=>'Jadwal Maintenance'],
            'karyawan'                => ['request_maint'=>'Request Maintenance','lacak_request'=>'Lacak Request'],
            'staff'                   => ['request_maint'=>'Ajukan Request','lacak_request'=>'Lacak Request'],
            default                   => ['request_maint'=>'Request Maintenance'],
        },

        // ───── MODUL ARSIP ─────
        // Hal.2: AdminArsip=Kelola Arsip, Backup, Klasifikasi, Pengaturan
        //        Manager=Dashboard, Laporan, Lihat Arsip
        //        Staff=Cari Arsip, Upload, Riwayat
        'arsip' => match($role) {
            'admin','admin_arsip' => ['kelola_arsip'=>'Kelola Arsip Digital','klasifikasi'=>'Kelola Klasifikasi Arsip','pencarian_arsip'=>'Pencarian Arsip','backup_restore'=>'Backup & Restore'],
            'manager'             => ['lihat_arsip'=>'Lihat Arsip','laporan_arsip'=>'Laporan Arsip'],
            'staff','karyawan'    => ['cari_arsip'=>'Cari Arsip','upload_dok'=>'Upload Dokumen','riwayat_arsip'=>'Riwayat Upload'],
            default               => ['cari_arsip'=>'Cari Arsip'],
        },

        // ───── MODUL LAPORAN ─────
        // Hal.1 Use Case: Manager=Dashboard Analitik, Generate Laporan Analitik
        'laporan' => match($role) {
            'admin'           => ['dashboard_analitik'=>'Dashboard Analitik','generate_laporan'=>'Generate Laporan Analitik','semua_laporan'=>'Semua Laporan'],
            'manager'         => ['dashboard_analitik'=>'Dashboard Analitik','generate_laporan'=>'Generate Laporan Analitik'],
            'admin_hr'        => ['laporan_sdm'=>'Laporan SDM','laporan_kinerja'=>'Laporan Kinerja'],
            'eo'              => ['laporan_event'=>'Laporan Event'],
            'admin_fasilitas' => ['laporan_maint'=>'Laporan Maintenance'],
            'admin_arsip'     => ['laporan_arsip'=>'Laporan Arsip'],
            default           => ['semua_laporan'=>'Semua Laporan'],
        },
    ];
}
