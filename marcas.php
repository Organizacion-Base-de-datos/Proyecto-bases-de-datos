<?php
require_once __DIR__ . '/includes/app.php';

$alertas = [];
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');

    if ($nombre === '') {
        $alertas[] = 'El nombre es obligatorio';
    }

    if (empty($alertas)) {
        $marca = new Marca(['nombre' => $nombre]);
        // Usamos crear() directo (ver nota sobre guardar() en la explicación)
        $resultado = $marca->crear();
        $exito = $resultado['resultado'];
    }
}

$marcas = Marca::all();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marcas</title>
</head>
<body>
    <h1>Registrar marca</h1>

    <?php foreach ($alertas as $alerta): ?>
        <p style="color:red;"><?php echo s($alerta); ?></p>
    <?php endforeach; ?>

    <?php if ($exito): ?>
        <p style="color:green;">Marca guardada correctamente</p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" placeholder="Ej. Bic">
        <input type="submit" value="Guardar">
    </form>

    <h2>Marcas registradas</h2>
    <ul>
        <?php foreach ($marcas as $m): ?>
            <li><?php echo s($m->id); ?> - <?php echo s($m->nombre); ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
