<?php
    require_once __DIR__ . '/../includes/app.php';
    requerirAcceso();

    $usuario = usuarioActual();
    $msg = $_GET['msg'] ?? '';

    // Descripción corta de cada módulo para las tarjetas
    $descripciones = [
        'ventas'      => 'Registrar y consultar ventas',
        'productos'   => 'Inventario y precios',
        'clientes'    => 'Alta y consulta de clientes',
        'proveedores' => 'Quién nos surte',
        'marcas'      => 'Marcas de los productos',
        'categorias'  => 'Categorías de los productos',
        'promociones' => 'Descuentos por categoría',
        'empleados'   => 'Personal y accesos',
        'roles'       => 'Roles del personal',
    ];

    // Resumen rápido (solo de lo que el usuario puede ver)
    $resumen = [];

    if (puedeVer('productos')) {
        $productos = Producto::all();
        $bajos = array_filter($productos, fn($p) => (int)$p->stock <= 5);
        $resumen[] = ['Productos registrados', count($productos), ''];
        $resumen[] = ['Con stock bajo (5 o menos)', count($bajos), count($bajos) > 0 ? 'alerta' : ''];
    }
    if (puedeVer('clientes')) {
        $resumen[] = ['Clientes', count(Cliente::all()), ''];
    }
    if (puedeVer('ventas')) {
        $hoy = date('Y-m-d');
        $ventasHoy = array_filter(Venta::all(), fn($v) => substr((string)$v->fecha, 0, 10) === $hoy);
        $totalHoy = array_sum(array_map(fn($v) => (float)$v->total, $ventasHoy));
        $resumen[] = ['Ventas de hoy', count($ventasHoy), ''];
        $resumen[] = ['Total vendido hoy', '$' . number_format($totalHoy, 2), ''];
    }

    require_once __DIR__ . '/../views/header.php';
?>

<div class="encabezado-pagina">
    <h1>Hola, <?php echo s(explode(' ', $usuario['nombre'])[0]); ?></h1>
</div>

<?php if ($msg === 'sin_permiso'): ?>
    <p class="aviso aviso-error">Tu rol no tiene permiso para entrar a esa sección.</p>
<?php endif; ?>

<?php if ($resumen): ?>
    <div class="resumen">
        <?php foreach ($resumen as [$etiqueta, $valor, $clase]): ?>
            <div class="dato <?php echo s($clase); ?>">
                <span class="dato-valor"><?php echo s((string)$valor); ?></span>
                <span class="dato-etiqueta"><?php echo s($etiqueta); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h2 class="subtitulo">Secciones</h2>
<div class="tarjetas">
    <?php foreach (modulosPermitidos() as $clave): ?>
        <a class="tarjeta-enlace" href="<?php echo s(urlSrc(MODULOS[$clave][1])); ?>">
            <h3><?php echo s(MODULOS[$clave][0]); ?></h3>
            <p><?php echo s($descripciones[$clave] ?? ''); ?></p>
        </a>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../views/footer.php'; ?>
