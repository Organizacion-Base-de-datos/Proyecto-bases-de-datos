<?php
    require_once __DIR__ . '/../../includes/app.php';
    requerirAcceso('categorias');

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
<?php require_once __DIR__ . '/../../views/header.php'; ?>

    <div class="encabezado-pagina">
        <h1>Categorías</h1>
        <button type="button" class="btn" id="btn-nuevo" aria-controls="panel-form">+ Nuevo</button>
    </div>

    <?php if (isset($mensajes[$msg])): ?>
        <p class="aviso aviso-<?php echo $mensajes[$msg][0] === 'green' ? 'ok' : 'error'; ?>"><?php echo s($mensajes[$msg][1]); ?></p>
    <?php endif; ?>

    <?php foreach ($alertas as $alerta): ?>
        <p class="aviso aviso-error aviso-form"><?php echo s($alerta); ?></p>
    <?php endforeach; ?>

    <?php if ($exito): ?>
        <p class="aviso aviso-ok">Categoría guardada correctamente</p>
    <?php endif; ?>

    <section class="tarjeta panel-form" id="panel-form">
        <h2><?php echo $id_editar ? 'Editar categoría' : 'Registrar categoría'; ?></h2>

    <form method="POST" action="categorias.php" class="form-registro">
        <?php if ($id_editar): ?>
            <input type="hidden" name="id" value="<?php echo s($id_editar); ?>">
        <?php endif; ?>

        <p>
            <label for="nombre">Nombre</label>
        <input type="text" id="nombre" name="nombre" maxlength="50" placeholder="Ej. Escritura" value="<?php echo s($nombre); ?>
        </p>">
        <span class="acciones-form"><button type="submit" class="btn"><?php echo $id_editar ? 'Actualizar' : 'Guardar'; ?></button>

        <?php if ($id_editar): ?>
            <a href="categorias.php" class="btn btn-sec">Cancelar</a>
        <?php endif; ?></span>
    </form>

    </section>

    <section class="tarjeta">
        <h2>Categorías registradas</h2>
    <div class="tabla-scroll">
    <table class="tabla">
        <tr><th>ID</th><th>Nombre</th><th>Acciones</th></tr>
        <?php foreach ($lista as $item): ?>
            <tr>
                <td><?php echo s($item->id); ?></td>
                <td><?php echo s($item->nombre); ?></td>
                <td>
                    <a href="categorias.php?editar=<?php echo s($item->id); ?>" class="btn btn-sec btn-sm">Editar</a>

                    <form method="POST" action="<?php echo s(urlSrc('delete/eliminar.php')); ?>" style="display:inline;"
                          onsubmit="return confirm('¿Seguro que quieres eliminar este registro?');">
                        <input type="hidden" name="tipo" value="categorias">
                        <input type="hidden" name="id" value="<?php echo s($item->id); ?>">
                        <button type="submit" class="btn btn-peligro btn-sm">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    </div>
    </section>
<?php require_once __DIR__ . '/../../views/footer.php'; ?>
