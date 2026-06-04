document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.sidebar-toggle, .app-sidebar-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelector('.app-sidebar')?.classList.toggle('open');
    });
  });
});
