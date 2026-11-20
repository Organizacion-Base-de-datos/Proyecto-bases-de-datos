<?php
    require_once __DIR__ . '/../../includes/app.php';
    requerirAcceso('promociones');

    $alertas = [];
    $exito = false;

    $categorias = Categoria::all();

    $vacio = [
        'nombre' => '', 'tipo' => '', 'descuento' => '', 'descripcion' => '',
        'fecha_inicio' => '', 'fecha_fin' => '', 'categorias' => [],
    ];
    $datos = $vacio;
    $id_editar = null;

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['editar'])) {
        $promo = ctype_digit((string)$_GET['editar']) ? Promocion::find((int)$_GET['editar']) : null;
        if ($promo) {
            $id_editar = (int)$promo->id;
            $marcadas = [];
            foreach (Categoria_promocion::belongsTo('id_promocion', $promo->id) as $rel) {
                $marcadas[] = (string)$rel->id_categoria;
            }
            $datos = [
                'nombre'       => $promo->nombre,
                'tipo'         => $promo->tipo,
                'descuento'    => $promo->descuento,
                'descripcion'  => $promo->descripcion,
                'fecha_inicio' => $promo->fecha_inicio,
                'fecha_fin'    => $promo->fecha_fin,
                'categorias'   => $marcadas,
            ];
        } else {
            $alertas[] = 'La promoción que quieres editar no existe';
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $idPost   = $_POST['id'] ?? '';
        $editando = $idPost !== '';

        if ($editando) {
            if (!ctype_digit((string)$idPost) || !Promocion::find((int)$idPost)) {
                $alertas[] = 'La promoción que intentas actualizar no existe';
            } else {
                $id_editar = (int)$idPost;
            }
        }

        $datos['nombre']       = trim($_POST['nombre'] ?? '');
        $datos['tipo']         = $_POST['tipo'] ?? '';
        $datos['descuento']    = trim($_POST['descuento'] ?? '');
        $datos['descripcion']  = trim($_POST['descripcion'] ?? '');
        $datos['fecha_inicio'] = $_POST['fecha_inicio'] ?? '';
        $datos['fecha_fin']    = $_POST['fecha_fin'] ?? '';
        $datos['categorias']   = array_map('strval', (array)($_POST['categorias'] ?? []));

        if ($datos['nombre'] === '') {
            $alertas[] = 'El nombre es obligatorio';
        } elseif (strlen($datos['nombre']) > 50) {
            $alertas[] = 'El nombre no puede pasar de 50 caracteres';
        }

        if (!in_array($datos['tipo'], ['Porcentaje', 'Monto fijo'], true)) {
            $alertas[] = 'Selecciona un tipo de promoción';
        }

        if (!is_numeric($datos['descuento']) || $datos['descuento'] <= 0 || $datos['descuento'] > 999.99) {
            $alertas[] = 'El descuento debe ser un número mayor a 0 (máximo 999.99)';
        } elseif ($datos['tipo'] === 'Porcentaje' && $datos['descuento'] > 100) {
            $alertas[] = 'Un porcentaje no puede ser mayor a 100';
        }

        if ($datos['fecha_inicio'] === '' || $datos['fecha_fin'] === '') {
            $alertas[] = 'Las fechas de inicio y fin son obligatorias';
        } elseif ($datos['fecha_fin'] < $datos['fecha_inicio']) {
            $alertas[] = 'La fecha fin no puede ser anterior a la fecha de inicio';
        }

        if (empty($datos['categorias'])) {
            $alertas[] = 'Selecciona al menos una categoría';
        } else {
            foreach ($datos['categorias'] as $idc) {
                if (!ctype_digit($idc) || !Categoria::find((int)$idc)) {
                    $alertas[] = 'Categoría inválida';
                    break;
                }
            }
        }

        if (empty($alertas)) {
            $valores = [
                'nombre'       => $datos['nombre'],
                'tipo'         => $datos['tipo'],
                'descuento'    => (float)$datos['descuento'],
                'descripcion'  => $datos['descripcion'],
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin'    => $datos['fecha_fin'],
            ];
            try {
                $db->beginTransaction();

                if ($editando) {
                    $promocion = Promocion::find($id_editar);
                    $promocion->sincronizar($valores);
                    $promocion->actualizar();
                    $id_promocion = $id_editar;

                    $borrar = $db->prepare("DELETE FROM Categorias_promociones WHERE id_promocion = :id");
                    $borrar->execute([':id' => $id_promocion]);
                } else {
                    $promocion = new Promocion($valores);
                    $resultado = $promocion->crear();
                    $id_promocion = (int)$resultado['id'];
                }

                foreach (array_unique($datos['categorias']) as $idc) {
                    $relacion = new Categoria_promocion([
                        'id_categoria' => (int)$idc,
                        'id_promocion' => $id_promocion,
                    ]);
                    $relacion->crear();
                }

                $db->commit();

                if ($editando) {
                    header('Location: promociones.php?msg=actualizado');
                    exit;
                }
                $exito = true;
                $datos = $vacio;
            } catch (PDOException $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $alertas[] = 'Error al guardar: ' . $e->getMessage();
            }
        }
    }

    $mensajes = [
        'actualizado' => ['green', 'Promoción actualizada correctamente'],
        'eliminado'   => ['green', 'Promoción eliminada correctamente'],
        'en_uso'      => ['red',   'No se puede eliminar: otros registros dependen de esta promoción'],
        'no_existe'   => ['red',   'El registro ya no existe'],
        'error'       => ['red',   'Ocurrió un error'],
    ];
    $msg = $_GET['msg'] ?? '';

    $promociones = Promocion::all();
?>
<?php require_once __DIR__ . '/../../views/header.php'; ?>

    <div class="encabezado-pagina">
        <h1>Promociones</h1>
        <button type="button" class="btn" id="btn-nuevo" aria-controls="panel-form">+ Nuevo</button>
    </div>

    <?php if (isset($mensajes[$msg])): ?>
        <p class="aviso aviso-<?php echo $mensajes[$msg][0] === 'green' ? 'ok' : 'error'; ?>"><?php echo s($mensajes[$msg][1]); ?></p>
    <?php endif; ?>

    <?php foreach ($alertas as $alerta): ?>
        <p class="aviso aviso-error aviso-form"><?php echo s($alerta); ?></p>
    <?php endforeach; ?>

    <?php if ($exito): ?>
        <p class="aviso aviso-ok">Promoción guardada correctamente</p>
    <?php endif; ?>

    <section class="tarjeta panel-form" id="panel-form">
        <h2><?php echo $id_editar ? 'Editar promoción' : 'Registrar promoción'; ?></h2>

    <?php if (empty($categorias)): ?>
        <p>Primero registra al menos una categoría en <a href="categorias.php">categorias.php</a>.</p>
    <?php else: ?>
    <form method="POST" action="promociones.php" class="form-registro">
        <?php if ($id_editar): ?>
            <input type="hidden" name="id" value="<?php echo s($id_editar); ?>">
        <?php endif; ?>

        <p>
            <label for="nombre">Nombre</label><br>
            <input type="text" id="nombre" name="nombre" maxlength="50" value="<?php echo s($datos['nombre']); ?>">
        </p>
        <p>
            <label for="tipo">Tipo</label><br>
            <select id="tipo" name="tipo">
                <option value="">-- Selecciona --</option>
                <option value="Porcentaje" <?php echo $datos['tipo'] === 'Porcentaje' ? 'selected' : ''; ?>>Porcentaje</option>
                <option value="Monto fijo" <?php echo $datos['tipo'] === 'Monto fijo' ? 'selected' : ''; ?>>Monto fijo</option>
            </select>
        </p>
        <p>
            <label for="descuento">Descuento</label><br>
            <input type="number" id="descuento" name="descuento" step="0.01" min="0.01" max="999.99" value="<?php echo s($datos['descuento']); ?>">
        </p>
        <p>
            <label for="descripcion">Descripción</label><br>
            <textarea id="descripcion" name="descripcion" rows="3" cols="40"><?php echo s($datos['descripcion']); ?></textarea>
        </p>
        <p>
            <label for="fecha_inicio">Fecha inicio</label><br>
            <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo s($datos['fecha_inicio']); ?>">
        </p>
        <p>
            <label for="fecha_fin">Fecha fin</label><br>
            <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo s($datos['fecha_fin']); ?>">
        </p>
        <fieldset>
            <legend>Aplica en estas categorías</legend>
            <?php foreach ($categorias as $c): ?>
                <label>
                    <input type="checkbox" name="categorias[]" value="<?php echo s($c->id); ?>"
                        <?php echo in_array((string)$c->id, $datos['categorias'], true) ? 'checked' : ''; ?>>
                    <?php echo s($c->nombre); ?>
                </label><br>
            <?php endforeach; ?>
        </fieldset>

        <p>
            <span class="acciones-form"><button type="submit" class="btn"><?php echo $id_editar ? 'Actualizar promoción' : 'Guardar promoción'; ?></button>
            <?php if ($id_editar): ?>
                <a href="promociones.php" class="btn btn-sec">Cancelar</a>
            <?php endif; ?></span>
        </p>
    </form>
    <?php endif; ?>

    </section>

    <section class="tarjeta">
        <h2>Promociones registradas</h2>
    <div class="tabla-scroll">
    <table class="tabla">
        <tr>
            <th>ID</th><th>Nombre</th><th>Tipo</th><th>Descuento</th>
            <th>Vigencia</th><th>Categorías</th><th>Acciones</th>
        </tr>
        <?php foreach ($promociones as $p): ?>
            <?php
                // Buscar en la tabla intermedia las categorías de esta promoción
                $nombresCategorias = [];
                foreach (Categoria_promocion::belongsTo('id_promocion', $p->id) as $rel) {
                    $cat = Categoria::find($rel->id_categoria);
                    if ($cat) {
                        $nombresCategorias[] = $cat->nombre;
                    }
                }
            ?>
            <tr>
                <td><?php echo s($p->id); ?></td>
                <td><?php echo s($p->nombre); ?></td>
                <td><?php echo s($p->tipo); ?></td>
                <td><?php echo s($p->descuento); ?><?php echo $p->tipo === 'Porcentaje' ? '%' : ''; ?></td>
                <td><?php echo s($p->fecha_inicio); ?> a <?php echo s($p->fecha_fin); ?></td>
                <td><?php echo s(implode(', ', $nombresCategorias)); ?></td>
                <td>
                    <a href="promociones.php?editar=<?php echo s($p->id); ?>" class="btn btn-sec btn-sm">Editar</a>

                    <form method="POST" action="<?php echo s(urlSrc('delete/eliminar.php')); ?>" style="display:inline;"
                          onsubmit="return confirm('¿Seguro que quieres eliminar esta promoción?');">
                        <input type="hidden" name="tipo" value="promociones">
                        <input type="hidden" name="id" value="<?php echo s($p->id); ?>">
                        <button type="submit" class="btn btn-peligro btn-sm">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    </div>
    </section>
<?php require_once __DIR__ . '/../../views/footer.php'; ?>
