<?php
    require_once __DIR__ . '/../../includes/app.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . urlSrc('index.php'));
        exit;
    }

    $tablas = [
        'empleados'   => ['clase' => 'Empleado', 'pagina' => 'empleados.php',   'etiqueta' => 'Empleado'],
        'proveedores' => ['clase' => 'Proveedor', 'pagina' => 'proveedores.php', 'etiqueta' => 'Proveedor'],
    ];

    $tipo = $_POST['tipo'] ?? '';
    $id   = $_POST['id'] ?? '';

    if (!isset($tablas[$tipo])) {
        http_response_code(400);
        exit('Tipo de registro inválido');
    }

    requerirAcceso($tipo);

    $clase  = $tablas[$tipo]['clase'];
    $pagina = $tablas[$tipo]['pagina'];

    function volver(string $pagina, string $msg): void {
        header("Location: " . urlSrc("create_update/{$pagina}") . "?msg={$msg}");
        exit;
    }

    if (!ctype_digit((string)$id)) {
        volver($pagina, 'error');
    }

    $registro = $clase::find((int)$id);
    if (!$registro) {
        volver($pagina, 'no_existe');
    }

    try {
        $registro->activo = $registro->activo ? '0' : '1';
        $registro->actualizar();

        volver($pagina, $registro->activo === '1' ? 'activado' : 'desactivado');
    } catch (PDOException $e) {
        volver($pagina, 'error');
    }
?>