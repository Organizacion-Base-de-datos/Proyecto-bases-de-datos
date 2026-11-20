<?php
    require_once __DIR__ . '/../../includes/app.php';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . urlSrc('index.php'));
        exit;
    }

    $tablas = [
        'marcas'      => ['clase' => 'Marca',     'pagina' => 'marcas.php'],
        'categorias'  => ['clase' => 'Categoria', 'pagina' => 'categorias.php'],
        'productos'   => ['clase' => 'Producto',  'pagina' => 'productos.php'],
        'promociones' => ['clase' => 'Promocion', 'pagina' => 'promociones.php'],
        'roles'       => ['clase' => 'Rol',       'pagina' => 'rol.php'],
        'clientes'    => ['clase' => 'Cliente',   'pagina' => 'clientes.php'],
        'ventas'      => ['clase' => 'Venta',     'pagina' => 'ventas.php'],
    ];

    function volver(string $pagina, string $msg): void {
        header("Location: " . urlSrc("create_update/{$pagina}") . "?msg={$msg}");
        exit;
    }

    $tipo = $_POST['tipo'] ?? '';
    $id   = $_POST['id'] ?? '';

    if (!isset($tablas[$tipo])) {
        http_response_code(400);
        exit('Tipo de registro inválido');
    }

    // El tipo de registro coincide con la clave del módulo (clientes, ventas, roles...)
    requerirAcceso($tipo);

    $clase  = $tablas[$tipo]['clase'];
    $pagina = $tablas[$tipo]['pagina'];

    if (!ctype_digit((string)$id)) {
        volver($pagina, 'error');
    }

    $registro = $clase::find((int)$id);
    if (!$registro) {
        volver($pagina, 'no_existe');
    }

    try {
        switch ($tipo) {

            case 'promociones':
                $db->beginTransaction();
                $stmt = $db->prepare("DELETE FROM Categorias_promociones WHERE id_promocion = :id");
                $stmt->execute([':id' => $registro->id]);
                $registro->eliminar();
                $db->commit();
                break;

            case 'ventas':
                $db->beginTransaction();
                $devolver = $db->prepare("UPDATE Productos SET stock = stock + 1 WHERE id = :id");
                foreach (Venta_producto::belongsTo('id_venta', $registro->id) as $detalle) {
                    $devolver->execute([':id' => $detalle->id_producto]);
                }
                $stmt = $db->prepare("DELETE FROM Ventas_productos WHERE id_venta = :id");
                $stmt->execute([':id' => $registro->id]);
                $registro->eliminar();
                $db->commit();
                break;

            default:
                $registro->eliminar();
        }

        volver($pagina, 'eliminado');

    } catch (PDOException $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        volver($pagina, $e->getCode() === '23503' ? 'en_uso' : 'error');
    }
?>