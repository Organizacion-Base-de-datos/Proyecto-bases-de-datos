<?php
    require_once __DIR__ . '/includes/app.php';

    $alertas = [];
    $exito = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre          = trim($_POST['nombre'] ?? '');
        $nombre_contacto = trim($_POST['nombre_contacto'] ?? '');
        $telefono        = trim($_POST['telefono'] ?? '');
        $correo          = trim($_POST['correo'] ?? '');
        $direccion       = trim($_POST['direccion'] ?? '');
        $rfc             = strtoupper(trim($_POST['rfc'] ?? ''));
        $activo          = isset($_POST['activo']) ? '1' : '0';

        if ($nombre === '') {
            $alertas[] = 'El nombre es obligatorio';
        }
        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $alertas[] = 'El correo no es válido';
        }
        if ($rfc !== '') {
            if (!in_array(strlen($rfc), [12, 13])) {
                $alertas[] = 'El RFC debe tener 12 o 13 caracteres';
            } elseif (Proveedor::where('rfc', $rfc)) {
                $alertas[] = 'Ya existe un proveedor con ese RFC';
            }
        }

        if (empty($alertas)) {
            $proveedor = new Proveedor([
                'nombre'          => $nombre,
                'nombre_contacto' => $nombre_contacto,
                'telefono'        => $telefono,
                'correo'          => $correo,
                'direccion'       => $direccion,
                'rfc'             => $rfc,
                'activo'          => $activo,
            ]);
            $resultado = $proveedor->crear();
            $exito = $resultado['resultado'];
        }
    }

    $proveedores = Proveedor::all();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedores</title>
</head>
<body>
    <h1>Registrar proveedor</h1>

    <?php foreach ($alertas as $alerta): ?>
        <p style="color:red;"><?php echo s($alerta); ?></p>
    <?php endforeach; ?>

    <?php if ($exito): ?>
        <p style="color:green;">Proveedor guardado correctamente</p>
    <?php endif; ?>

    <form method="POST" action="">
        <p>
            <label for="nombre">Nombre</label><br>
            <input type="text" id="nombre" name="nombre">
        </p>
        <p>
            <label for="nombre_contacto">Nombre de contacto</label><br>
            <input type="text" id="nombre_contacto" name="nombre_contacto">
        </p>
        <p>
            <label for="telefono">Teléfono</label><br>
            <input type="tel" id="telefono" name="telefono">
        </p>
        <p>
            <label for="correo">Correo</label><br>
            <input type="email" id="correo" name="correo">
        </p>
        <p>
            <label for="direccion">Dirección</label><br>
            <input type="text" id="direccion" name="direccion">
        </p>
        <p>
            <label for="rfc">RFC</label><br>
            <input type="text" id="rfc" name="rfc" maxlength="13">
        </p>
        <p>
            <label>
                <input type="checkbox" name="activo" value="1" checked> Activo
            </label>
        </p>
        <input type="submit" value="Guardar proveedor">
    </form>

    <h2>Proveedores registrados</h2>
    <table border="1" cellpadding="4">
        <tr>
            <th>ID</th><th>Nombre</th><th>Contacto</th><th>Teléfono</th>
            <th>Correo</th><th>RFC</th><th>Dirección</th><th>Activo</th>
        </tr>
        <?php foreach ($proveedores as $prov): ?>
        <tr>
            <td><?php echo s($prov->id); ?></td>
            <td><?php echo s($prov->nombre); ?></td>
            <td><?php echo s($prov->nombre_contacto); ?></td>
            <td><?php echo s($prov->telefono); ?></td>
            <td><?php echo s($prov->correo); ?></td>
            <td><?php echo s($prov->rfc); ?></td>
            <td><?php echo s($prov->direccion); ?></td>
            <td><?php echo $prov->activo ? 'Sí' : 'No'; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>