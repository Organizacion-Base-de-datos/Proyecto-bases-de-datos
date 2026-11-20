<?php
    require_once __DIR__ . '../../../includes/app.php';

    $alertas = [];
    $exito = false;

    $id_editar = null;
    $nombre = '';

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['editar'])) {
        $registro = ctype_digit((string)$_GET['editar']) ? Categoria::find((int)$_GET['editar']) : null;
        if ($registro) {
            $id_editar = (int)$registro->id;
            $nombre = $registro->nombre;
        } else {
            $alertas[] = 'La categoría que quieres editar no existe';
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre   = trim($_POST['nombre'] ?? '');
        $idPost   = $_POST['id'] ?? '';
        $editando = $idPost !== '';

        if ($editando) {
            if (!ctype_digit((string)$idPost) || !Categoria::find((int)$idPost)) {
                $alertas[] = 'La categoría que intentas actualizar no existe';
            } else {
                $id_editar = (int)$idPost;
            }
        }

        if ($nombre === '') {
            $alertas[] = 'El nombre es obligatorio';
        } elseif (strlen($nombre) > 50) {
            $alertas[] = 'El nombre no puede pasar de 50 caracteres';
        } else {
            $existente = Categoria::where('nombre', $nombre);
            if ($existente && (int)$existente->id !== (int)$id_editar) {
                $alertas[] = 'Ya existe una categoría con ese nombre';
            }
        }

        if (empty($alertas)) {
            try {
                if ($editando) {
                    $registro = Categoria::find($id_editar);
                    $registro->sincronizar(['nombre' => $nombre]);
                    $registro->actualizar();
                    header('Location: categorias.php?msg=actualizado');
                    exit;
                }

                $registro = new Categoria(['nombre' => $nombre]);
                $resultado = $registro->crear();
                $exito = $resultado['resultado'];
                $nombre = '';
            } catch (PDOException $e) {
                $alertas[] = 'Error al guardar: ' . $e->getMessage();
            }
        }
    }

    $mensajes = [
        'actualizado' => ['green', 'Categoría actualizada correctamente'],
        'eliminado'   => ['green', 'Categoría eliminada correctamente'],
        'en_uso'      => ['red',   'No se puede eliminar: hay productos o promociones que usan esta categoría'],
        'no_existe'   => ['red',   'El registro ya no existe'],
        'error'       => ['red',   'Ocurrió un error'],
    ];
    $msg = $_GET['msg'] ?? '';

    $lista = Categoria::all();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías</title>
</head>
<body>
    <h1><?php echo $id_editar ? 'Editar categoría' : 'Registrar categoría'; ?></h1>

    <?php if (isset($mensajes[$msg])): ?>
        <p style="color:<?php echo $mensajes[$msg][0]; ?>;"><?php echo s($mensajes[$msg][1]); ?></p>
    <?php endif; ?>

    <?php foreach ($alertas as $alerta): ?>
        <p style="color:red;"><?php echo s($alerta); ?></p>
    <?php endforeach; ?>

    <?php if ($exito): ?>
        <p style="color:green;">Categoría guardada correctamente</p>
    <?php endif; ?>

    <form method="POST" action="categorias.php">
        <?php if ($id_editar): ?>
            <input type="hidden" name="id" value="<?php echo s($id_editar); ?>">
        <?php endif; ?>

        <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" maxlength="50" placeholder="Ej. Escritura" value="<?php echo s($nombre); ?>">
        <input type="submit" value="<?php echo $id_editar ? 'Actualizar' : 'Guardar'; ?>">

        <?php if ($id_editar): ?>
            <a href="categorias.php">Cancelar</a>
        <?php endif; ?>
    </form>

    <h2>Categorías registradas</h2>
    <table border="1" cellpadding="4">
        <tr><th>ID</th><th>Nombre</th><th>Acciones</th></tr>
        <?php foreach ($lista as $item): ?>
            <tr>
                <td><?php echo s($item->id); ?></td>
                <td><?php echo s($item->nombre); ?></td>
                <td>
                    <a href="categorias.php?editar=<?php echo s($item->id); ?>">Editar</a>

                    <form method="POST" action="eliminar.php" style="display:inline;"
                          onsubmit="return confirm('¿Seguro que quieres eliminar este registro?');">
                        <input type="hidden" name="tipo" value="categorias">
                        <input type="hidden" name="id" value="<?php echo s($item->id); ?>">
                        <input type="submit" value="Eliminar">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
