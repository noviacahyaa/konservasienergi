<?php
// Sesuaikan dengan lingkungan Anda
date_default_timezone_set('Asia/Jakarta');   // WIB; ganti Asia/Makassar (WITA) atau Asia/Jayapura (WIT) jika perlu
define('BASE_URL', '/konservasi_energi_noviana');   // nama folder di htdocs

define('LOGIN_NIS_OTOMATIS', true);

$host = 'localhost';
$db   = 'konservasi_energi_noviana';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    exit('Koneksi database gagal. Periksa config/database.php');
}
