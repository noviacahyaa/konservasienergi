<?php
require_once __DIR__ . '/../config/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect($path) { header('Location: ' . BASE_URL . '/' . $path); exit; }

function flash($type = null, $msg = null) {
    if ($type !== null) { $_SESSION['flash'] = [$type, $msg]; return null; }
    if (isset($_SESSION['flash'])) { $f = $_SESSION['flash']; unset($_SESSION['flash']); return $f; }
    return null;
}

function csrf() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . csrf() . '">'; }
function csrf_check() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Permintaan tidak valid.');
    }
}

function user() { return $_SESSION['user'] ?? null; }
function is_siswa() { return (user()['level'] ?? '') === 'Siswa'; }

function login_wajib(array $level = []) {
    if (empty($_SESSION['user'])) redirect('login.php');
    if ($level && !in_array($_SESSION['user']['level'], $level, true)) {
        http_response_code(403);
        exit('Anda tidak punya akses ke halaman ini.');
    }
}

// Ambil checklist; siswa hanya boleh membuka miliknya sendiri (username = NIS)
function ambil_checklist(PDO $pdo, $id) {
    $st = $pdo->prepare('SELECT c.*, s.nama, s.kelas FROM checklist c JOIN siswa s ON s.nis = c.nis WHERE c.id_checklist = ?');
    $st->execute([(int)$id]);
    $c = $st->fetch();
    if (!$c) { flash('danger', 'Checklist tidak ditemukan.'); redirect('checklist/'); }
    if (is_siswa() && (string)$c['nis'] !== (string)(user()['nis'] ?? '')) {
        http_response_code(403);
        exit('Ini bukan checklist Anda.');
    }
    return $c;
}

// ---------- Tampilan ----------

// Ikon SVG sebaris (stroke mengikuti warna teks). Dipakai di sidebar dan kartu.
function ikon($nama) {
    $p = [
        'menu'   => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'user'   => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
        'home'   => '<path d="M3 11l9-8 9 8M5 10v10h14V10"/>',
        'siswa'  => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.5 3-5.5 7-5.5s7 2 7 5.5M17 5a3.5 3.5 0 010 7M22 20c0-2.5-1.5-4-4-5"/>',
        'bolt'   => '<path d="M13 2L4 14h7l-1 8 9-12h-7z"/>',
        'list'   => '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h3"/>',
        'shield' => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/>',
        'key'    => '<circle cx="8" cy="15" r="4"/><path d="M11 12l9-9M16 7l3 3"/>',
        'moon'   => '<path d="M20 14A8 8 0 019.5 3.5 8 8 0 1020 14z"/>',
        'info'   => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5v.5"/>',
        'out'    => '<path d="M9 4H5v16h4M16 8l4 4-4 4M20 12H9"/>',
        'chev'   => '<path d="M6 9l6 6 6-6"/>',
        'set'    => '<path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/>',
        'check'  => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/>',
        'arrow'  => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'leaf'   => '<path d="M5 19C5 10 10 5 20 5c0 10-5 15-14 15M5 19c3-5 6-8 10-10"/>',
        'tip'    => '<path d="M9 18h6M10 21h4M12 3a6 6 0 00-4 10.5c.8.8 1 1.5 1 2.5h6c0-1 .2-1.7 1-2.5A6 6 0 0012 3z"/>',
        'speaker'=> '<path d="M3 10v4h4l6 4V6L7 10zM17 8a5 5 0 010 8"/>',
    ][$nama] ?? '';
    return '<svg class="ikon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
}

// Mencari file logo di assets/img/ dengan nama konservasi.(png|jpg|jpeg|webp|svg); urutan = prioritas.
function logo_file() {
    foreach (['png', 'jpg', 'jpeg', 'webp', 'svg'] as $x) {
        if (is_file(__DIR__ . '/../assets/img/konservasi.' . $x)) return 'konservasi.' . $x;
    }
    return null;
}

// Logo aplikasi: pakai file logo jika ada, kalau tidak pakai ikon petir.
function logo($ukuran = 36) {
    $f = logo_file();
    if ($f) {
        return '<img src="' . BASE_URL . '/assets/img/' . $f . '" alt="Logo Konservasi Energi" width="' . $ukuran . '" height="' . $ukuran . '" class="logo-img">';
    }
    return logo_svg($ukuran);
}

// Halaman aktif ditentukan dari alamat file yang sedang dibuka (untuk menyorot menu).
function halaman_aktif() {
    $s = $_SERVER['SCRIPT_NAME'] ?? '';
    foreach (['siswa', 'perangkat', 'checklist', 'users', 'akun'] as $k) {
        if (strpos($s, "/$k/") !== false) return $k;
    }
    return strpos($s, 'tentang') !== false ? 'tentang' : 'dashboard';
}

function menu_item($kunci, $url, $ikon, $label, $aktif) {
    $on = $kunci === $aktif ? ' on' : '';
    return '<a class="nv' . $on . '" href="' . BASE_URL . '/' . $url . '">' . ikon($ikon) . '<span>' . e($label) . '</span></a>';
}

// Tanggal berbahasa Indonesia, contoh: 15 Okt 2026
function tgl_id($tanggal) {
    $bln = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($tanggal);
    return date('j', $t) . ' ' . $bln[(int)date('n', $t)] . ' ' . date('Y', $t);
}

// Panjang teks; memakai mbstring bila tersedia (tidak semua server mengaktifkannya).
function panjang($s) { return function_exists('mb_strlen') ? mb_strlen($s) : strlen($s); }

// Membuat akun Siswa (tabel users) dari data di tabel siswa. Dipakai saat siswa pertama kali masuk
// dengan username = NIS dan password = NIS. Mengembalikan baris users, atau null jika NIS tidak terdaftar.
function buat_akun_siswa(PDO $pdo, $nis) {
    $st = $pdo->prepare('SELECT nis, nama FROM siswa WHERE nis = ?');
    $st->execute([(int)$nis]);
    $sw = $st->fetch();
    if (!$sw || (string)$sw['nis'] !== (string)$nis) return null;   // tolak mis. "012" (angka nol di depan)
    try {
        $pdo->prepare('INSERT INTO users (username, password, nama, level, nis) VALUES (?,?,?,?,?)')
            ->execute([(string)$nis, password_hash((string)$nis, PASSWORD_DEFAULT), $sw['nama'], 'Siswa', $sw['nis']]);
    } catch (PDOException $e) {
        return null;                                                  // mis. username sudah dipakai
    }
    $st = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $st->execute([(string)$nis]);
    return $st->fetch() ?: null;
}

// Logo lingkaran: petir di dalam daun (SVG sebaris, tanpa file gambar).
function logo_svg($px = 40) {
    return '<svg class="logo-svg" width="' . $px . '" height="' . $px . '" viewBox="0 0 64 64" role="img" aria-label="Logo Konservasi Energi Noviana">'
        . '<circle cx="32" cy="32" r="30" fill="#db2777"/>'
        . '<path d="M14 40C14 24 24 14 46 14c0 22-10 32-26 32" fill="#f9a8d4" opacity=".9"/>'
        . '<path d="M35 12 22 35h9l-3 17 15-24h-9z" fill="#fbbf24" stroke="#fff" stroke-width="2" stroke-linejoin="round"/>'
        . '</svg>';
}

// Ilustrasi hero: bohlam berdaun di tengah lingkaran, dengan sinar dan panel surya kecil.
function ilustrasi_hero($px = 220) {
    return '<svg class="ilu" width="' . $px . '" height="' . $px . '" viewBox="0 0 240 240" aria-hidden="true">'
        . '<circle cx="120" cy="120" r="108" style="fill:var(--ilu-bg)"/><circle cx="120" cy="120" r="78" style="fill:var(--ilu-bg)"/>'
        . '<g stroke="#fbbf24" stroke-width="6" stroke-linecap="round"><path d="M120 18v16M52 46l11 11M188 46l-11 11M26 112h16M198 112h16"/></g>'
        . '<path d="M120 50c-30 0-52 21-52 50 0 18 10 29 19 40 5 6 7 11 7 18h52c0-7 2-12 7-18 9-11 19-22 19-40 0-29-22-50-52-50z" fill="#fde68a" stroke="#fff" stroke-width="4" stroke-linejoin="round"/>'
        . '<path d="M120 142v-38M120 104c-10-2-17-9-18-18M120 104c10-2 17-9 18-18" stroke="#db2777" stroke-width="6" fill="none" stroke-linecap="round"/>'
        . '<rect x="94" y="160" width="52" height="12" rx="6" fill="#cbd5e1" stroke="#fff" stroke-width="3"/>'
        . '<rect x="100" y="176" width="40" height="12" rx="6" fill="#cbd5e1" stroke="#fff" stroke-width="3"/>'
        . '<path d="M110 192h20l-3 9h-14z" fill="#94a3b8"/>'
        . '<g transform="translate(170 168) skewX(-12)"><rect width="52" height="34" rx="4" fill="#be185d" stroke="#fff" stroke-width="3"/>'
        . '<path d="M17 0v34M35 0v34M0 17h52" stroke="#fff" stroke-width="2" opacity=".7"/></g>'
        . '</svg>';
}

// Ilustrasi kecil untuk kartu tips: colokan listrik berdaun.
function ilustrasi_colokan($px = 120) {
    return '<svg class="ilu" width="' . $px . '" height="' . $px . '" viewBox="0 0 120 120" aria-hidden="true">'
        . '<circle cx="60" cy="60" r="54" fill="rgba(219,39,119,.12)"/>'
        . '<rect x="38" y="48" width="44" height="34" rx="10" fill="#db2777"/>'
        . '<path d="M48 48V30M72 48V30" stroke="#94a3b8" stroke-width="7" stroke-linecap="round"/>'
        . '<path d="M60 82v18c0 6 6 10 12 10" stroke="#db2777" stroke-width="6" fill="none" stroke-linecap="round"/>'
        . '<path d="M78 26c10-2 18 4 20 14-10 2-18-4-20-14z" fill="#f9a8d4"/><path d="M78 26c6 4 12 8 17 12" stroke="#db2777" stroke-width="2.5" fill="none" stroke-linecap="round"/>'
        . '<path d="M64 56 54 70h7l-2 11 11-15h-7z" fill="#fbbf24"/>'
        . '</svg>';
}
