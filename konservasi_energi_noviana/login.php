<?php
require_once __DIR__ . '/includes/functions.php';
if (user()) redirect('index.php');           // sudah login -> ke dashboard

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();                              // tolak form yang bukan berasal dari halaman ini
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $st = $pdo->prepare('SELECT * FROM users WHERE username = ?');   // prepared statement: aman dari SQL injection
    $st->execute([$username]);
    $row = $st->fetch();

    // Siswa yang ada di tabel siswa tetapi belum punya akun: username = NIS, password awal = NIS.
    $akunBaru = false;
    if (!$row && LOGIN_NIS_OTOMATIS && ctype_digit($username) && strlen($username) <= 10 && hash_equals($username, $password)) {
        $row = buat_akun_siswa($pdo, $username);
        $akunBaru = (bool)$row;
    }

    // password_verify membandingkan password yang diketik dengan hash di database
    if ($row && password_verify($password, $row['password'])) {
        session_regenerate_id(true);           // ganti ID sesi setelah login (cegah session fixation)
        $_SESSION['user'] = ['id' => $row['id_user'], 'username' => $row['username'], 'nis' => $row['nis'],
                             'nama' => $row['nama'], 'level' => $row['level']];
        if ($akunBaru) flash('warning', 'Akun Anda dibuat otomatis dengan password awal = NIS. Segera ganti di menu Pengaturan > Ganti password.');
        redirect('index.php');
    }
    $err = 'Username atau password salah.';   // pesan sengaja sama untuk username salah / password salah
}
?>
<!doctype html>
<html lang="id" data-bs-theme="light">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Masuk | Konservasi Energi Noviana</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/app.css?v=<?= @filemtime(__DIR__ . '/assets/css/app.css') ?>" rel="stylesheet">
  <script>try { var t = localStorage.getItem('tema'); if (t) document.documentElement.setAttribute('data-bs-theme', t); } catch (e) {}</script>
</head>
<body>
<div class="login-bg">
  <div class="login-box">
    <div class="login-kiri">
      <?= logo(52) ?>
      <h2>Konservasi Energi Noviana</h2>
      <div class="tag">Mulai dari Kita!</div>
      <p>Catat penggunaan perangkat listrik di sekolah, pantau kepatuhannya, dan hemat energi bersama.</p>
      <?= ilustrasi_hero(170) ?>
    </div>
    <div class="login-kanan">
      <h4 class="mb-1">Selamat datang</h4>
      <div class="muted mb-3">Masuk untuk mengisi checklist hemat energi.</div>
      <?php if (LOGIN_NIS_OTOMATIS): ?><div class="muted small mb-3">Siswa: username dan password awal adalah NIS.</div><?php endif; ?>
      <?php if ($err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endif; ?>
      <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label">Username</label>
          <input name="username" class="form-control" required autofocus></div>
        <div class="mb-4"><label class="form-label">Password</label>
          <input name="password" type="password" class="form-control" required></div>
        <button class="btn btn-success w-100 py-2">Masuk</button>
      </form>
    </div>
  </div>
</div>
</body></html>
