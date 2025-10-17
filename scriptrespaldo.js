document.addEventListener('DOMContentLoaded', () => {

  // ===== Fecha y hora =====
  const elementoFechaHora = document.getElementById('fecha-hora');
  function actualizarFechaHora() {
    if (!elementoFechaHora) return;
    const ahora = new Date();
    const fecha = ahora.toLocaleDateString('es-AR', { day: '2-digit', month: '2-digit', year: 'numeric' });
    const hora = ahora.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    elementoFechaHora.textContent = `Fecha: ${fecha} - Hora: ${hora}`;
  }
  actualizarFechaHora();
  setInterval(actualizarFechaHora, 1000);

  // ===== Tema claro/oscuro =====
  const botonTema = document.getElementById('toggle-theme');
  if (botonTema) {
    botonTema.addEventListener('click', () => {
      document.body.classList.toggle('dark');
      botonTema.textContent = document.body.classList.contains('dark') ? '☀️' : '🌙';
      localStorage.setItem('tema', document.body.classList.contains('dark') ? 'oscuro' : 'claro');
    });

    const temaGuardado = localStorage.getItem('tema');
    if (temaGuardado === 'oscuro') {
      document.body.classList.add('dark');
      botonTema.textContent = '☀️';
    }
  }

  // ===== Barra lateral hover =====
  const zonaHover = document.querySelector('.zona-hover');
  const barra = document.getElementById('barraLateral');
  if (zonaHover && barra) {
    zonaHover.addEventListener('mouseenter', () => barra.classList.add('mostrar-barra'));
    barra.addEventListener('mouseleave', () => barra.classList.remove('mostrar-barra'));
  }

  // ===== Barra lateral móvil (opcional botón) =====
  const btnMenu = document.getElementById('btn-menu');
  if (btnMenu && barra) {
    btnMenu.addEventListener('click', () => barra.classList.toggle('mostrar-barra'));
  }

});