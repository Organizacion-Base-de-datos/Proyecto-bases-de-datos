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

document.addEventListener('DOMContentLoaded', function () {

    // ===== Menú deslizante =====
    var html = document.documentElement;
    var boton = document.getElementById('btn-menu');
    var fondo = document.getElementById('fondo-menu');
    var grande = window.matchMedia('(min-width: 900px)');

    function menuVisible() {
        return grande.matches ? !html.classList.contains('menu-cerrado') : html.classList.contains('menu-abierto');
    }

    function actualizarBoton() {
        if (boton) boton.setAttribute('aria-expanded', menuVisible() ? 'true' : 'false');
    }

    function alternarMenu() {
        if (grande.matches) {
            var cerrado = html.classList.toggle('menu-cerrado');
            try { localStorage.setItem('menu-cerrado', cerrado ? '1' : '0'); } catch (e) {}
        } else {
            html.classList.toggle('menu-abierto');
        }
        actualizarBoton();
    }

    function cerrarMenuChico() {
        html.classList.remove('menu-abierto');
        actualizarBoton();
    }

    if (boton) boton.addEventListener('click', alternarMenu);
    if (fondo) fondo.addEventListener('click', cerrarMenuChico);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !grande.matches) cerrarMenuChico();
    });
    grande.addEventListener('change', function () {
        html.classList.remove('menu-abierto');
        actualizarBoton();
    });
    actualizarBoton();

    // ===== Botón "Nuevo": el formulario de alta/edición se muestra solo cuando hace falta =====
    var panel = document.getElementById('panel-form');
    var botonNuevo = document.getElementById('btn-nuevo');

    var hayFormulario = panel && panel.querySelector('.form-registro');

    if (panel && botonNuevo && hayFormulario) {
        var editando = panel.querySelector('input[type="hidden"][name="id"]');
        var hayErrores = document.querySelector('.aviso-form');

        // Se muestra abierto si se está editando o si hubo errores al guardar
        function abrir() {
            panel.hidden = false;
            botonNuevo.setAttribute('aria-expanded', 'true');
        }
        function cerrar() {
            panel.hidden = true;
            botonNuevo.setAttribute('aria-expanded', 'false');
        }

        if (editando || hayErrores) abrir(); else cerrar();

        botonNuevo.addEventListener('click', function () {
            if (panel.hidden) {
                abrir();
                var primero = panel.querySelector('input:not([type="hidden"]), select, textarea');
                if (primero) primero.focus();
            } else {
                cerrar();
            }
        });
    } else if (botonNuevo) {
        // No hay formulario (por ejemplo, faltan datos previos): el aviso queda a la vista y no hace falta el botón
        botonNuevo.hidden = true;
    }
});
