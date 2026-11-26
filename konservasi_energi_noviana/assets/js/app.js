// Mode gelap: Bootstrap 5.3 membaca atribut data-bs-theme pada <html>. Pilihan disimpan di localStorage.
(function () {
  var html = document.documentElement;
  var btnTema = document.getElementById('btnTema');
  if (!btnTema) return;

  function setSwitch() {
    var gelap = html.getAttribute('data-bs-theme') === 'dark';
    btnTema.setAttribute('aria-checked', gelap ? 'true' : 'false');
    btnTema.classList.toggle('aktif', gelap);
  }
  btnTema.addEventListener('click', function (e) {
    e.stopPropagation();   // dropdown tetap terbuka saat saklar ditekan
    var baru = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-bs-theme', baru);
    try { localStorage.setItem('tema', baru); } catch (err) {}
    setSwitch();
  });
  setSwitch();
})();
