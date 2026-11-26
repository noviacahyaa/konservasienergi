<?php
require_once __DIR__ . '/../includes/functions.php';
login_wajib();

// Query sama persis dengan checklist/index.php (termasuk pencarian & pembatasan untuk Siswa)
$q = trim($_GET['q'] ?? '');
$sql = 'SELECT c.id_checklist, c.tanggal, c.jam, s.nis, s.nama, s.kelas,
               COUNT(d.id_detail) AS jml_item, SUM(d.status = "Ya") AS jml_ya
        FROM checklist c
        JOIN siswa s ON s.nis = c.nis
        LEFT JOIN detail_checklist d ON d.id_checklist = c.id_checklist
        WHERE (s.nama LIKE :a OR s.kelas LIKE :b OR c.tanggal LIKE :c)';
$par = [':a' => "%$q%", ':b' => "%$q%", ':c' => "%$q%"];
if (is_siswa()) { $sql .= ' AND c.nis = :nis'; $par[':nis'] = (int)(user()['nis'] ?? 0); }
$sql .= ' GROUP BY c.id_checklist ORDER BY c.tanggal DESC, c.jam DESC';
$st = $pdo->prepare($sql);
$st->execute($par);
$data = $st->fetchAll();

$nama_file = 'checklist_konservasi_noviana_' . date('Ymd_His') . '.xls';
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nama_file . '"');
header('Cache-Control: max-age=0');
echo "\xEF\xBB\xBF"; // BOM agar huruf UTF-8 terbaca benar
?>
<html><head><meta charset="utf-8"></head><body>
<table border="1">
  <tr><th colspan="8">Checklist Konservasi Energi Noviana</th></tr>
  <tr><th colspan="8">Dicetak: <?= e(tgl_id(date('Y-m-d'))) ?> <?= e(date('H:i')) ?></th></tr>
  <tr style="background:#db2777;color:#ffffff">
    <th>No</th><th>Tanggal</th><th>Jam</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Item Ya</th><th>Total Item</th>
  </tr>
  <?php foreach ($data as $i => $r): ?>
  <tr>
    <td><?= $i + 1 ?></td>
    <td style="mso-number-format:'\@'"><?= e($r['tanggal']) ?></td>
    <td style="mso-number-format:'\@'"><?= e(substr($r['jam'], 0, 5)) ?></td>
    <td style="mso-number-format:'\@'"><?= e($r['nis']) ?></td>
    <td><?= e($r['nama']) ?></td>
    <td><?= e($r['kelas']) ?></td>
    <td><?= (int)$r['jml_ya'] ?></td>
    <td><?= (int)$r['jml_item'] ?></td>
  </tr>
  <?php endforeach; ?>
</table>
</body></html>
