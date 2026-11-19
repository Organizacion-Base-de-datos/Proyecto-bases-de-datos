<?php
    require_once __DIR__ . '/includes/app.php';

    $alertas = [];
    $exito = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = trim($_POST['nombre'] ?? '');

        if ($nombre === '') {
            $alertas[] = 'El nombre del rol es obligatorio';
        } elseif (strlen($nombre) > 50) {
            $alertas[] = 'El nombre no puede pasar de 50 caracteres';
        } elseif (Rol::where('nombre', $nombre)) {
            $alertas[] = 'Ya existe un rol con ese nombre';
        }

        if (empty($alertas)) {
            $rol = new Rol(['nombre' => $nombre]);
            $resultado = $rol->crear();
            $exito = $resultado['resultado'];
        }
    }

    $roles = Rol::all();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>roles</title>
</head>
<body>
    <h1>Registrar roles</h1>

    <?php foreach ($alertas as $alerta): ?>
        <p style="color:red;"><?php echo s($alerta); ?></p>
    <?php endforeach; ?>

    <?php if ($exito): ?>
        <p style="color:green;">rol guardado correctamente</p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="nombre">Nombre del rol</label>
        <input type="text" id="nombre" name="nombre" maxlength="50" placeholder="Ej. Vendedor">
        <input type="submit" value="Guardar">
    </form>

    <h2>Roles registrados</h2>
    <ul>
        <?php foreach ($roles as $rol): ?>
            <li><?php echo s($rol->id); ?> - <?php echo s($rol->nombre); ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>