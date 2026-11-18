<?php
require_once __DIR__ . '/includes/app.php';

$alertas = [];
$exito = false;

$categorias = Categoria::all();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre       = trim($_POST['nombre'] ?? '');
    $tipo         = $_POST['tipo'] ?? '';
    $descuento    = $_POST['descuento'] ?? '';
    $descripcion  = trim($_POST['descripcion'] ?? '');
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin    = $_POST['fecha_fin'] ?? '';
    $ids_categorias = $_POST['categorias'] ?? [];

    if ($nombre === '') {
        $alertas[] = 'El nombre es obligatorio';
    } elseif (strlen($nombre) > 50) {
        $alertas[] = 'El nombre no puede pasar de 50 caracteres';
    }

    if (!in_array($tipo, ['Porcentaje', 'Monto fijo'], true)) {
        $alertas[] = 'Selecciona un tipo de promoción';
    }

    if (!is_numeric($descuento) || $descuento <= 0 || $descuento > 999.99) {
        $alertas[] = 'El descuento debe ser un número mayor a 0 (máximo 999.99)';
    } elseif ($tipo === 'Porcentaje' && $descuento > 100) {
        $alertas[] = 'Un porcentaje no puede ser mayor a 100';
    }

    if ($fecha_inicio === '' || $fecha_fin === '') {
        $alertas[] = 'Las fechas de inicio y fin son obligatorias';
    } elseif ($fecha_fin < $fecha_inicio) {
        $alertas[] = 'La fecha fin no puede ser anterior a la fecha de inicio';
    }

    if (empty($ids_categorias)) {
        $alertas[] = 'Selecciona al menos una categoría';
    } else {
        foreach ($ids_categorias as $idc) {
            if (!ctype_digit((string)$idc)) {
                $alertas[] = 'Categoría inválida';
                break;
            }
        }
    }

    if (empty($alertas)) {
        try {
            $db->beginTransaction();

            $promocion = new Promocion([
                'nombre'       => $nombre,
                'tipo'         => $tipo,
                'descuento'    => (float)$descuento,
                'descripcion'  => $descripcion,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin'    => $fecha_fin,
            ]);
            $resultado = $promocion->crear();
            $id_promocion = (int)$resultado['id'];

            foreach ($ids_categorias as $idc) {
                $relacion = new Categoria_promocion([
                    'id_categoria' => (int)$idc,
                    'id_promocion' => $id_promocion,
                ]);
                $relacion->crear();
            }

            $db->commit();
            $exito = true;
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $alertas[] = 'Error al guardar: ' . $e->getMessage();
        }
    }
}

$promociones = Promocion::all();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promociones</title>
</head>
<body>
    <h1>Registrar promoción</h1>

    <?php foreach ($alertas as $alerta): ?>
        <p style="color:red;"><?php echo s($alerta); ?></p>
    <?php endforeach; ?>

    <?php if ($exito): ?>
        <p style="color:green;">Promoción guardada correctamente</p>
    <?php endif; ?>

    <?php if (empty($categorias)): ?>
        <p>Primero registra al menos una categoría en <a href="categorias.php">categorias.php</a>.</p>
    <?php else: ?>
    <form method="POST" action="">
        <p>
            <label for="nombre">Nombre</label><br>
            <input type="text" id="nombre" name="nombre" maxlength="50">
        </p>
        <p>
            <label for="tipo">Tipo</label><br>
            <select id="tipo" name="tipo">
                <option value="">-- Selecciona --</option>
                <option value="Porcentaje">Porcentaje</option>
                <option value="Monto fijo">Monto fijo</option>
            </select>
        </p>
        <p>
            <label for="descuento">Descuento</label><br>
            <input type="number" id="descuento" name="descuento" step="0.01" min="0.01" max="999.99">
        </p>
        <p>
            <label for="descripcion">Descripción</label><br>
            <textarea id="descripcion" name="descripcion" rows="3" cols="40"></textarea>
        </p>
        <p>
            <label for="fecha_inicio">Fecha inicio</label><br>
            <input type="date" id="fecha_inicio" name="fecha_inicio">
        </p>
        <p>
            <label for="fecha_fin">Fecha fin</label><br>
            <input type="date" id="fecha_fin" name="fecha_fin">
        </p>
        <fieldset>
            <legend>Aplica en estas categorías</legend>
            <?php foreach ($categorias as $c): ?>
                <label>
                    <input type="checkbox" name="categorias[]" value="<?php echo s($c->id); ?>">
                    <?php echo s($c->nombre); ?>
                </label><br>
            <?php endforeach; ?>
        </fieldset>
        <p><input type="submit" value="Guardar promoción"></p>
    </form>
    <?php endif; ?>

    <h2>Promociones registradas</h2>
    <table border="1" cellpadding="4">
        <tr>
            <th>ID</th><th>Nombre</th><th>Tipo</th><th>Descuento</th>
            <th>Vigencia</th><th>Categorías</th>
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
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>