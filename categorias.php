<?php
    require_once __DIR__ . '/includes/app.php';

    $alertas = [];
    $exito = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = trim($_POST['nombre'] ?? '');

        if ($nombre === '') {
            $alertas[] = 'El nombre es obligatorio';
        } elseif (strlen($nombre) > 50) {
            $alertas[] = 'El nombre no puede pasar de 50 caracteres';
        } elseif (Categoria::where('nombre', $nombre)) {
            $alertas[] = 'Ya existe una categoría con ese nombre';
        }

        if (empty($alertas)) {
            $categoria = new Categoria(['nombre' => $nombre]);
            $resultado = $categoria->crear();
            $exito = $resultado['resultado'];
        }
    }

    $categorias = Categoria::all();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías</title>
</head>
<body>
    <h1>Registrar categoría</h1>

    <?php foreach ($alertas as $alerta): ?>
        <p style="color:red;"><?php echo s($alerta); ?></p>
    <?php endforeach; ?>

    <?php if ($exito): ?>
        <p style="color:green;">Categoría guardada correctamente</p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" maxlength="50" placeholder="Ej. Escritura">
        <input type="submit" value="Guardar">
    </form>

    <h2>Categorías registradas</h2>
    <ul>
        <?php foreach ($categorias as $c): ?>
            <li><?php echo s($c->id); ?> - <?php echo s($c->nombre); ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>