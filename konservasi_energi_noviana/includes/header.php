<?php
// Kerangka halaman: navbar atas + strip judul/pencarian + area isi. Dipakai semua halaman setelah login.
require_once __DIR__ . '/functions.php';
$u     = user();
$f     = flash();
$aktif = halaman_aktif();
$level = $u['level'] ?? '';
?>
<!doctype html>
<html lang="id" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($judul ?? 'Beranda') ?> | Konservasi Energi Noviana</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/app.css?v=<?= @filemtime(__DIR__ . '/../assets/css/app.css') ?>" rel="stylesheet">
  <script>
    // Dijalankan sebelum halaman tampil agar tidak "berkedip": ambil tema yang tersimpan.
    try { var t = localStorage.getItem('tema'); if (t) document.documentElement.setAttribute('data-bs-theme', t); } catch (e) {}
  </script>
</head>
<body>
<nav class="topnav">
  <div class="wrap nav-in">
    <a class="brand" href="<?= BASE_URL ?>/index.php"><?= logo(40) ?><span><b>Konservasi Energi</b><small>Noviana</small></span></a>
    <button class="navtoggle" type="button" data-bs-toggle="collapse" data-bs-target="#menuUtama" aria-controls="menuUtama" aria-expanded="false" aria-label="Buka atau tutup menu"><?= ikon('menu') ?></button>

    <div class="collapse menu-wrap" id="menuUtama">
      <div class="menu-list">
        <?= menu_item('dashboard', 'index.php', 'home', 'Beranda', $aktif) ?>
        <?php if (in_array($level, ['Admin', 'Guru'], true)): ?>
          <?= menu_item('siswa', 'siswa/', 'siswa', 'Siswa', $aktif) ?>
          <?= menu_item('perangkat', 'perangkat/', 'bolt', 'Perangkat', $aktif) ?>
          <?= menu_item('checklist', 'checklist/', 'list', 'Checklist', $aktif) ?>
        <?php else: ?>
          <?= menu_item('checklist', 'checklist/', 'list', 'Checklist saya', $aktif) ?>
        <?php endif; ?>
        <?php if ($level === 'Admin'): ?>
          <?= menu_item('users', 'users/', 'shield', 'Pengguna', $aktif) ?>
        <?php endif; ?>
        <?= menu_item('tips', 'index.php#tips', 'tip', 'Tips Hemat Energi', $aktif) ?>
        <?= menu_item('tentang', 'tentang.php', 'info', 'Tentang Kami', $aktif) ?>

        <!-- Pengaturan: dropdown berisi Ganti password dan Mode gelap -->
        <div class="dropdown">
          <button class="nv nv-btn <?= $aktif === 'akun' ? 'on' : '' ?>" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?= ikon('set') ?><span>Pengaturan</span><?= ikon('chev') ?>
          </button>
          <ul class="dropdown-menu shadow">
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/akun/password.php"><?= ikon('key') ?> Ganti password</a></li>
            <li><button class="dropdown-item" type="button" id="btnTema" role="switch" aria-checked="false"><?= ikon('moon') ?> Mode gelap <span class="sw"></span></button></li>
          </ul>
        </div>
      </div>

      <div class="dropdown akun-box">
        <button class="user-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="avatar"><?= ikon('user') ?></span>
          <span class="user-nama"><b><?= e($u['nama'] ?? '') ?></b><small><?= e($level) ?></small></span>
          <?= ikon('chev') ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow">
          <li class="dropdown-header"><?= e($u['nama'] ?? '') ?> (<?= e($level) ?>)</li>
          <li><a class="dropdown-item" href="<?= BASE_URL ?>/akun/password.php"><?= ikon('key') ?> Ganti password</a></li>
          <li><a class="dropdown-item" href="<?= BASE_URL ?>/logout.php"><?= ikon('out') ?> Logout</a></li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<div class="strip">
  <div class="wrap strip-in">
    <div class="strip-judul"><small>Konservasi Energi Noviana</small><strong><?= e($judul ?? 'Beranda') ?></strong></div>
    <form class="cari" action="<?= BASE_URL ?>/checklist/" method="get" role="search">
      <?= ikon('search') ?>
      <input type="search" name="q" placeholder="Cari checklist (nama, kelas, tanggal)..." aria-label="Cari checklist">
    </form>
  </div>
</div>

<main class="wrap page">
<?php if ($f): ?>
  <div class="alert alert-<?= e($f[0]) ?> alert-dismissible fade show"><?= e($f[1]) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
