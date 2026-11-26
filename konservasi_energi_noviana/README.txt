KONSERVASI ENERGI NOVIANA
=========================
Isi dan fitur sama dengan Konservasi Energi Renata, dengan tampilan berbeda
(navbar atas, tema pink gradasi putih, grafik batang, halaman login dua panel).

CARA PASANG
1. Ekstrak zip, pindahkan folder "konservasi_energi_noviana" ke C:\xampp\htdocs\
2. Nyalakan Apache dan MySQL di XAMPP.
3. phpMyAdmin > tab Import > pilih konservasi_energi_noviana.sql > Go
   (membuat database konservasi_energi_noviana beserta data contoh).
4. Buka http://localhost/konservasi_energi_noviana/

AKUN AWAL (segera ganti lewat Pengaturan > Ganti password)
  admin / admin123   akses penuh
  guru  / guru123    siswa, perangkat, checklist (tanpa menu Pengguna, tanpa hapus)
  siswa / siswa123   checklist miliknya sendiri (terhubung ke NIS 2526003, Novi)
Siswa lain: username dan password awal = NIS (mis. 2526001), akun dibuat otomatis saat pertama masuk.

PENGATURAN
  config/database.php : nama database, user, password, BASE_URL, zona waktu.

FITUR
  Beranda (statistik, grafik 7 hari, tips), Siswa, Perangkat, Checklist (+ detail, isi massal,
  cari, ekspor Excel dan PDF), Pengguna (Admin), Ganti password, Mode gelap, Tentang Kami.

STRUKTUR
  index.php, login.php, logout.php, tentang.php
  siswa/ perangkat/ checklist/ users/ akun/   modul halaman
  includes/header.php, footer.php, functions.php
  assets/css/app.css (tampilan), assets/js/app.js
  konservasi_energi_noviana.sql   database + data contoh
