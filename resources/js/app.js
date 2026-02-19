// Navigation toggle (defensiv + DOMContentLoaded)
document.addEventListener('DOMContentLoaded', function () {
  var nav = document.getElementById('primary-menu');
  var toggle = document.getElementById('primary-menu-toggle');

  if (!toggle || !nav) return; // pagina nu are meniul => ieșim liniștiți

  toggle.addEventListener('click', function (e) {
    e.preventDefault();
    nav.classList.toggle('hidden');
  });
});