<?php
require_once __DIR__ . '/includes/app.php';

$alertas = [];
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre       = trim($_POST['nombre'] ?? '');
    $stock        = $_POST['stock'] ?? '';
    $precio_costo = $_POST['precio_costo'] ?? '';
    $precio_venta = $_POST['precio_venta'] ?? '';
    $id_marca     = $_POST['id_marca'] ?? '';
    $id_categoria = $_POST['id_categoria'] ?? '';
    $id_proveedor = $_POST['id_proveedor'] ?? '';

    if ($nombre === '') $alertas[] = 'El nombre es obligatorio';
    if (!ctype_digit((string)$stock)) $alertas[] = 'El stock debe ser un entero';
    if (!is_numeric($precio_costo)) $alertas[] = 'El precio de costo debe ser numérico';
    if (!is_numeric($precio_venta)) $alertas[] = 'El precio de venta debe ser numérico';
    if (!ctype_digit((string)$id_marca)) $alertas[] = 'Selecciona una marca';
    if (!ctype_digit((string)$id_categoria)) $alertas[] = 'Selecciona una categoría';
    if (!ctype_digit((string)$id_proveedor)) $alertas[] = 'Selecciona un proveedor';

    if (empty($alertas)) {
        $producto = new Producto([
            'nombre'       => $nombre,
            'stock'        => (int)$stock,
            'precio_costo' => (float)$precio_costo,
            'precio_venta' => (float)$precio_venta,
            'id_marca'     => (int)$id_marca,
            'id_categoria' => (int)$id_categoria,
            'id_proveedor' => (int)$id_proveedor,
        ]);
        $resultado = $producto->crear();
        $exito = $resultado['resultado'];
    }
}

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
    <h1>Registrar producto</h1>

    <?php foreach ($alertas as $alerta): ?>
        <p style="color:red;"><?php echo s($alerta); ?></p>
    <?php endforeach; ?>

    <?php if ($exito): ?>
        <p style="color:green;">Producto guardado correctamente</p>
    <?php endif; ?>

    <form method="POST" action="">
        <p>
            <label>Nombre</label><br>
            <input type="text" name="nombre">
        </p>
        <p>
            <label>Stock</label><br>
            <input type="number" name="stock" min="0">
        </p>
        <p>
            <label>Precio costo</label><br>
            <input type="number" name="precio_costo" step="0.01" min="0">
        </p>
        <p>
            <label>Precio venta</label><br>
            <input type="number" name="precio_venta" step="0.01" min="0">
        </p>
        <p>
            <label>Marca</label><br>
            <select name="id_marca">
                <option value="">-- Selecciona --</option>
                <?php foreach ($marcas as $m): ?>
                    <option value="<?php echo s($m->id); ?>"><?php echo s($m->nombre); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label>Categoría</label><br>
            <select name="id_categoria">
                <option value="">-- Selecciona --</option>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?php echo s($c->id); ?>"><?php echo s($c->nombre); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label>Proveedor</label><br>
            <select name="id_proveedor">
                <option value="">-- Selecciona --</option>
                <?php foreach ($proveedores as $p): ?>
                    <option value="<?php echo s($p->id); ?>"><?php echo s($p->nombre); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <input type="submit" value="Guardar producto">
    </form>

    <h2>Productos registrados</h2>
    <table border="1" cellpadding="4">
        <tr><th>ID</th><th>Nombre</th><th>Stock</th><th>Costo</th><th>Venta</th></tr>
        <?php foreach ($productos as $p): ?>
            <tr>
                <td><?php echo s($p->id); ?></td>
                <td><?php echo s($p->nombre); ?></td>
                <td><?php echo s($p->stock); ?></td>
                <td><?php echo s($p->precio_costo); ?></td>
                <td><?php echo s($p->precio_venta); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
