<?php
    require_once __DIR__ . '/includes/app.php';

    date_default_timezone_set('America/Mexico_City');

    $alertas = [];
    $exito = false;
    $totalVenta = 0;

    $clientes = Cliente::all();
    $empleados = array_filter(Empleado::all(), fn($e) => $e->activo);
    $productos = Producto::SQL("SELECT * FROM Productos WHERE stock > 0 ORDER BY nombre");

    $id_cliente_sel = '';
    $id_empleado_sel = '';
    $productos_sel = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_cliente_sel  = $_POST['id_cliente'] ?? '';
        $id_empleado_sel = $_POST['id_empleado'] ?? '';
        $productos_sel   = $_POST['productos'] ?? [];

        if (!ctype_digit((string)$id_cliente_sel) || !Cliente::find((int)$id_cliente_sel)) {
            $alertas[] = 'Selecciona un cliente válido';
        }
        if (!ctype_digit((string)$id_empleado_sel) || !Empleado::find((int)$id_empleado_sel)) {
            $alertas[] = 'Selecciona un empleado válido';
        }
        if (empty($productos_sel)) {
            $alertas[] = 'Selecciona al menos un producto';
        } else {
            foreach ($productos_sel as $idp) {
                if (!ctype_digit((string)$idp)) {
                    $alertas[] = 'Producto inválido';
                    break;
                }
            }
        }

        if (empty($alertas)) {
            try {
                $db->beginTransaction();

                $total = 0;
                $items = [];
                foreach (array_unique($productos_sel) as $idp) {
                    $producto = Producto::find((int)$idp);
                    if (!$producto) {
                        throw new Exception('Un producto seleccionado ya no existe');
                    }
                    $total += (float)$producto->precio_venta;
                    $items[] = $producto;
                }

                $venta = new Venta([
                    'fecha'       => date('Y-m-d H:i:s'),
                    'total'       => round($total, 2),
                    'id_cliente'  => (int)$id_cliente_sel,
                    'id_empleado' => (int)$id_empleado_sel,
                ]);
                $resultado = $venta->crear();
                $id_venta = (int)$resultado['id'];

                $descuento = $db->prepare(
                    "UPDATE Productos SET stock = stock - 1 WHERE id = :id AND stock > 0"
                );
                foreach ($items as $producto) {
                    $detalle = new Venta_producto([
                        'id_venta'    => $id_venta,
                        'id_producto' => (int)$producto->id,
                    ]);
                    $detalle->crear();

                    $descuento->execute([':id' => $producto->id]);
                    if ($descuento->rowCount() === 0) {
                        throw new Exception('Sin stock suficiente de: ' . $producto->nombre);
                    }
                }

                $db->commit();
                $exito = true;
                $totalVenta = round($total, 2);
                $id_cliente_sel = '';
                $id_empleado_sel = '';
                $productos_sel = [];
                $productos = Producto::SQL("SELECT * FROM Productos WHERE stock > 0 ORDER BY nombre");
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $alertas[] = 'No se pudo guardar la venta: ' . $e->getMessage();
            }
        }
    }

    $ventas = Venta::SQL("SELECT * FROM Ventas ORDER BY fecha DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas</title>
</head>
<body>
    <h1>Registrar venta</h1>

    <?php foreach ($alertas as $alerta): ?>
        <p style="color:red;"><?php echo s($alerta); ?></p>
    <?php endforeach; ?>

    <?php if ($exito): ?>
        <p style="color:green;">Venta guardada correctamente. Total: $<?php echo number_format($totalVenta, 2); ?></p>
    <?php endif; ?>

    <?php if (empty($clientes) || empty($empleados) || empty($productos)): ?>
        <p>
            Para registrar una venta necesitas al menos:
            un <a href="clientes.php">cliente</a>,
            un <a href="empleados.php">empleado activo</a> y
            un <a href="productos.php">producto con stock</a>.
        </p>
    <?php else: ?>
    <form method="POST" action="">
        <p>
            <label for="id_cliente">Cliente</label><br>
            <select id="id_cliente" name="id_cliente">
                <option value="">-- Selecciona --</option>
                <?php foreach ($clientes as $c): ?>
                    <option value="<?php echo s($c->id); ?>" <?php echo ((string)$id_cliente_sel === (string)$c->id) ? 'selected' : ''; ?>>
                        <?php echo s($c->nombre); ?> (<?php echo s($c->rfc); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="id_empleado">Empleado que atiende</label><br>
            <select id="id_empleado" name="id_empleado">
                <option value="">-- Selecciona --</option>
                <?php foreach ($empleados as $e): ?>
                    <option value="<?php echo s($e->id); ?>" <?php echo ((string)$id_empleado_sel === (string)$e->id) ? 'selected' : ''; ?>>
                        <?php echo s($e->nombre . ' ' . $e->apellido); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <fieldset>
            <legend>Productos</legend>
            <?php foreach ($productos as $p): ?>
                <label>
                    <input type="checkbox" name="productos[]"
                           value="<?php echo s($p->id); ?>"
                           data-precio="<?php echo s($p->precio_venta); ?>"
                           <?php echo in_array((string)$p->id, array_map('strval', $productos_sel), true) ? 'checked' : ''; ?>>
                    <?php echo s($p->nombre); ?> —
                    $<?php echo number_format((float)$p->precio_venta, 2); ?>
                    (stock: <?php echo s($p->stock); ?>)
                </label><br>
            <?php endforeach; ?>
        </fieldset>

        <p><strong>Total: $<span id="total">0.00</span></strong></p>
        <input type="submit" value="Registrar venta">
    </form>

    <script>
        const checks = document.querySelectorAll('input[name="productos[]"]');
        const totalEl = document.getElementById('total');
        function actualizarTotal() {
            let total = 0;
            checks.forEach(c => { if (c.checked) total += parseFloat(c.dataset.precio); });
            totalEl.textContent = total.toFixed(2);
        }
        checks.forEach(c => c.addEventListener('change', actualizarTotal));
        actualizarTotal();
    </script>
    <?php endif; ?>

    <h2>Ventas registradas</h2>
    <table border="1" cellpadding="4">
        <tr>
            <th>ID</th><th>Fecha</th><th>Cliente</th><th>Empleado</th><th>Productos</th><th>Total</th>
        </tr>
        <?php foreach ($ventas as $v): ?>
            <?php
                $cli = Cliente::find($v->id_cliente);
                $emp = Empleado::find($v->id_empleado);
                $nombresProductos = [];
                foreach (Venta_producto::belongsTo('id_venta', $v->id) as $det) {
                    $prod = Producto::find($det->id_producto);
                    if ($prod) {
                        $nombresProductos[] = $prod->nombre;
                    }
                }
            ?>
            <tr>
                <td><?php echo s($v->id); ?></td>
                <td><?php echo s($v->fecha); ?></td>
                <td><?php echo $cli ? s($cli->nombre) : '—'; ?></td>
                <td><?php echo $emp ? s($emp->nombre . ' ' . $emp->apellido) : '—'; ?></td>
                <td><?php echo s(implode(', ', $nombresProductos)); ?></td>
                <td>$<?php echo number_format((float)$v->total, 2); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>