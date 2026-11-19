// Funciones compartidas por todas las pantallas

// Reemplaza al alert(): muestra un mensaje arriba a la derecha por 3 segundos
function mostrarMensaje(texto, color) {
    var caja = document.getElementById('mensaje-global');
    if (!caja) return;
    caja.textContent = texto;
    caja.style.background = color || '#2ecc71';
    caja.style.display = 'block';
    setTimeout(function () { caja.style.display = 'none'; }, 3000);
}
