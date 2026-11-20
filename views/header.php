<?php
// Cabecera común: abre la página, dibuja el menú deslizante y deja listo el <main>.
// Cada página la incluye con:  require_once __DIR__ . '/../../views/header.php';
// y al final incluye views/footer.php.

$usuario = usuarioActual();
$permitidos = modulosPermitidos();

// Qué módulo está abierto (rol.php -> 'roles'); en index.php no hay ninguno
$archivo = basename($_SERVER['SCRIPT_NAME'], '.php');
$moduloActivo = $archivo === 'rol' ? 'roles' : $archivo;
$tituloPagina = MODULOS[$moduloActivo][0] ?? 'Inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo s($tituloPagina); ?> · Papelería</title>
    <link rel="stylesheet" href="<?php echo s(urlSrc('css/estilos.css')); ?>">
    <script>
        // Recuerda si el menú estaba cerrado (en pantallas grandes) antes de dibujar, para que no parpadee
        try {
            if (localStorage.getItem('menu-cerrado') === '1' && window.matchMedia('(min-width: 900px)').matches) {
                document.documentElement.classList.add('menu-cerrado');
            }
        } catch (e) {}
    </script>
</head>
<body>
<div id="mensaje-global" class="msg-global" role="status"></div>

<div class="app">

    <aside class="menu-lateral" id="menu-lateral" aria-label="Menú principal">
        <div class="logo">Papelería</div>

        <nav>
            <a href="<?php echo s(urlSrc('index.php')); ?>" class="<?php echo $moduloActivo === 'index' ? 'activo' : ''; ?>">Inicio</a>
            <?php foreach ($permitidos as $clave): ?>
                <a href="<?php echo s(urlSrc(MODULOS[$clave][1])); ?>"
                   class="<?php echo $moduloActivo === $clave ? 'activo' : ''; ?>"
                   <?php echo $moduloActivo === $clave ? 'aria-current="page"' : ''; ?>>
                    <?php echo s(MODULOS[$clave][0]); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="usuario">
            <strong><?php echo s($usuario['nombre'] ?? ''); ?></strong>
            <span><?php echo s($usuario['rol'] ?? ''); ?></span>
            <a href="<?php echo s(urlSrc('logout.php')); ?>">Cerrar sesión</a>
        </div>
    </aside>

    <div class="fondo-menu" id="fondo-menu"></div>

    <div class="principal">
        <header class="barra-superior">
            <button type="button" class="btn-menu" id="btn-menu" aria-controls="menu-lateral" aria-expanded="true" aria-label="Abrir o cerrar el menú">
                <span></span><span></span><span></span>
            </button>
            <span class="barra-titulo"><?php echo s($tituloPagina); ?></span>
        </header>

        <main class="contenido">
