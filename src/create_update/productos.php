<?php
    require_once __DIR__ . '../../../includes/app.php';

    $alertas = [];
    $exito = false;

    $vacio = [
        'nombre' => '', 'stock' => '', 'precio_costo' => '', 'precio_venta' => '',
        'id_marca' => '', 'id_categoria' => '', 'id_proveedor' => '',
    ];
    $datos = $vacio;
    $id_editar = null;

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['editar'])) {
        $prod = ctype_digit((string)$_GET['editar']) ? Producto::find((int)$_GET['editar']) : null;
        if ($prod) {
            $id_editar = (int)$prod->id;
            $datos = [
                'nombre'       => $prod->nombre,
                'stock'        => $prod->stock,
                'precio_costo' => $prod->precio_costo,
                'precio_venta' => $prod->precio_venta,
                'id_marca'     => $prod->id_marca,
                'id_categoria' => $prod->id_categoria,
                'id_proveedor' => $prod->id_proveedor,
            ];
        } else {
            $alertas[] = 'El producto que quieres editar no existe';
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $idPost   = $_POST['id'] ?? '';
        $editando = $idPost !== '';

        if ($editando) {
            if (!ctype_digit((string)$idPost) || !Producto::find((int)$idPost)) {
                $alertas[] = 'El producto que intentas actualizar no existe';
            } else {
                $id_editar = (int)$idPost;
            }
        }

        $datos['nombre']       = trim($_POST['nombre'] ?? '');
        $datos['stock']        = trim($_POST['stock'] ?? '');
        $datos['precio_costo'] = trim($_POST['precio_costo'] ?? '');
        $datos['precio_venta'] = trim($_POST['precio_venta'] ?? '');
        $datos['id_marca']     = $_POST['id_marca'] ?? '';
        $datos['id_categoria'] = $_POST['id_categoria'] ?? '';
        $datos['id_proveedor'] = $_POST['id_proveedor'] ?? '';

        if ($datos['nombre'] === '' || strlen($datos['nombre']) > 100) {
            $alertas[] = 'El nombre es obligatorio (máximo 100 caracteres)';
        }
        if (!ctype_digit((string)$datos['stock'])) {
            $alertas[] = 'El stock debe ser un número entero (0 o más)';
        }
        if (!is_numeric($datos['precio_costo']) || $datos['precio_costo'] < 0 || $datos['precio_costo'] > 99999999.99) {
            $alertas[] = 'El precio de costo debe ser un número válido';
        }
        if (!is_numeric($datos['precio_venta']) || $datos['precio_venta'] < 0 || $datos['precio_venta'] > 99999999.99) {
            $alertas[] = 'El precio de venta debe ser un número válido';
        }
        if (!ctype_digit((string)$datos['id_marca']) || !Marca::find((int)$datos['id_marca'])) {
            $alertas[] = 'Selecciona una marca válida';
        }
        if (!ctype_digit((string)$datos['id_categoria']) || !Categoria::find((int)$datos['id_categoria'])) {
            $alertas[] = 'Selecciona una categoría válida';
        }
        if (!ctype_digit((string)$datos['id_proveedor']) || !Proveedor::find((int)$datos['id_proveedor'])) {
            $alertas[] = 'Selecciona un proveedor válido';
        }

        if (empty($alertas)) {
            $valores = [
                'nombre'       => $datos['nombre'],
                'stock'        => (int)$datos['stock'],
                'precio_costo' => (float)$datos['precio_costo'],
                'precio_venta' => (float)$datos['precio_venta'],
                'id_marca'     => (int)$datos['id_marca'],
                'id_categoria' => (int)$datos['id_categoria'],
                'id_proveedor' => (int)$datos['id_proveedor'],
            ];
            try {
                if ($editando) {
                    $producto = Producto::find($id_editar);
                    $producto->sincronizar($valores);
                    $producto->actualizar();
                    header('Location: productos.php?msg=actualizado');
                    exit;
                }

                $producto = new Producto($valores);
                $resultado = $producto->crear();
                $exito = $resultado['resultado'];
                if ($exito) {
                    $datos = $vacio;
                }
            } catch (PDOException $e) {
                $alertas[] = 'Error al guardar: ' . $e->getMessage();
            }
        }
    }

    $mensajes = [
        'actualizado' => ['green', 'Producto actualizado correctamente'],
        'eliminado'   => ['green', 'Producto eliminado correctamente'],
        'en_uso'      => ['red',   'No se puede eliminar: el producto ya aparece en ventas'],
        'no_existe'   => ['red',   'El registro ya no existe'],
        'error'       => ['red',   'Ocurrió un error'],
    ];
    $msg = $_GET['msg'] ?? '';

    $marcas = Marca::all();
    $categorias = Categoria::all();
    $proveedores = Proveedor::all();
    $productos = Producto::all();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos</title>
</head>
<body>
    <h1><?php echo $id_editar ? 'Editar producto' : 'Registrar producto'; ?></h1>

    <?php if (isset($mensajes[$msg])): ?>
        <p style="color:<?php echo $mensajes[$msg][0]; ?>;"><?php echo s($mensajes[$msg][1]); ?></p>
    <?php endif; ?>

    <?php foreach ($alertas as $alerta): ?>
        <p style="color:red;"><?php echo s($alerta); ?></p>
    <?php endforeach; ?>

    <?php if ($exito): ?>
        <p style="color:green;">Producto guardado correctamente</p>
    <?php endif; ?>

    <form method="POST" action="productos.php">
        <?php if ($id_editar): ?>
            <input type="hidden" name="id" value="<?php echo s($id_editar); ?>">
        <?php endif; ?>

        <p>
            <label for="nombre">Nombre</label><br>
            <input type="text" id="nombre" name="nombre" maxlength="100" value="<?php echo s($datos['nombre']); ?>">
        </p>
        <p>
            <label for="stock">Stock</label><br>
            <input type="number" id="stock" name="stock" min="0" value="<?php echo s($datos['stock']); ?>">
        </p>
        <p>
            <label for="precio_costo">Precio costo</label><br>
            <input type="number" id="precio_costo" name="precio_costo" step="0.01" min="0" value="<?php echo s($datos['precio_costo']); ?>">
        </p>
        <p>
            <label for="precio_venta">Precio venta</label><br>
            <input type="number" id="precio_venta" name="precio_venta" step="0.01" min="0" value="<?php echo s($datos['precio_venta']); ?>">
        </p>
        <p>
            <label for="id_marca">Marca</label><br>
            <select id="id_marca" name="id_marca">
                <option value="">-- Selecciona --</option>
                <?php foreach ($marcas as $m): ?>
                    <option value="<?php echo s($m->id); ?>" <?php echo ((string)$datos['id_marca'] === (string)$m->id) ? 'selected' : ''; ?>>
                        <?php echo s($m->nombre); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="id_categoria">Categoría</label><br>
            <select id="id_categoria" name="id_categoria">
                <option value="">-- Selecciona --</option>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?php echo s($c->id); ?>" <?php echo ((string)$datos['id_categoria'] === (string)$c->id) ? 'selected' : ''; ?>>
                        <?php echo s($c->nombre); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="id_proveedor">Proveedor</label><br>
            <select id="id_proveedor" name="id_proveedor">
                <option value="">-- Selecciona --</option>
                <?php foreach ($proveedores as $p): ?>
                    <option value="<?php echo s($p->id); ?>" <?php echo ((string)$datos['id_proveedor'] === (string)$p->id) ? 'selected' : ''; ?>>
                        <?php echo s($p->nombre); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <input type="submit" value="<?php echo $id_editar ? 'Actualizar producto' : 'Guardar producto'; ?>">
        <?php if ($id_editar): ?>
            <a href="productos.php">Cancelar</a>
        <?php endif; ?>
    </form>

    <h2>Productos registrados</h2>
    <table border="1" cellpadding="4">
        <thead>
            <tr>
                <th>ID</th><th>Nombre</th><th>Stock</th><th>Costo</th><th>Venta</th><th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $producto): ?>
                <tr>
                    <td><?php echo s($producto->id); ?></td>
                    <td><?php echo s($producto->nombre); ?></td>
                    <td><?php echo s($producto->stock); ?></td>
                    <td><?php echo s($producto->precio_costo); ?></td>
                    <td><?php echo s($producto->precio_venta); ?></td>
                    <td>
                        <a href="productos.php?editar=<?php echo s($producto->id); ?>">Editar</a>

                        <form method="POST" action="eliminar.php" style="display:inline;"
                              onsubmit="return confirm('¿Seguro que quieres eliminar este producto?');">
                            <input type="hidden" name="tipo" value="productos">
                            <input type="hidden" name="id" value="<?php echo s($producto->id); ?>">
                            <input type="submit" value="Eliminar">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
