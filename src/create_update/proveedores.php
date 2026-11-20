<?php
    require_once __DIR__ . '../../../includes/app.php';

    $alertas = [];
    $exito = false;

    $vacio = [
        'nombre' => '', 'nombre_contacto' => '', 'telefono' => '',
        'correo' => '', 'direccion' => '', 'rfc' => '', 'activo' => '1',
    ];
    $datos = $vacio;
    $id_editar = null;

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['editar'])) {
        $prov = ctype_digit((string)$_GET['editar']) ? Proveedor::find((int)$_GET['editar']) : null;
        if ($prov) {
            $id_editar = (int)$prov->id;
            $datos = [
                'nombre'          => $prov->nombre,
                'nombre_contacto' => $prov->nombre_contacto,
                'telefono'        => $prov->telefono,
                'correo'          => $prov->correo,
                'direccion'       => $prov->direccion,
                'rfc'             => $prov->rfc,
                'activo'          => $prov->activo ? '1' : '0',
            ];
        } else {
            $alertas[] = 'El proveedor que quieres editar no existe';
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (($_POST['accion'] ?? '') === 'cambiar_estado') {
            $id = $_POST['id'] ?? '';
            $msg = 'error';

            if (ctype_digit((string)$id)) {
                $prov = Proveedor::find((int)$id);
                if ($prov) {
                    $prov->activo = $prov->activo ? '0' : '1';
                    try {
                        $prov->actualizar();
                        $msg = $prov->activo === '1' ? 'activado' : 'desactivado';
                    } catch (PDOException $e) {
                        $msg = 'error';
                    }
                } else {
                    $msg = 'no_existe';
                }
            }
            header("Location: proveedores.php?msg={$msg}");
            exit;
        }

        $idPost   = $_POST['id'] ?? '';
        $editando = $idPost !== '';

        if ($editando) {
            if (!ctype_digit((string)$idPost) || !Proveedor::find((int)$idPost)) {
                $alertas[] = 'El proveedor que intentas actualizar no existe';
            } else {
                $id_editar = (int)$idPost;
            }
        }

        $datos['nombre']          = trim($_POST['nombre'] ?? '');
        $datos['nombre_contacto'] = trim($_POST['nombre_contacto'] ?? '');
        $datos['telefono']        = trim($_POST['telefono'] ?? '');
        $datos['correo']          = trim($_POST['correo'] ?? '');
        $datos['direccion']       = trim($_POST['direccion'] ?? '');
        $datos['rfc']             = strtoupper(trim($_POST['rfc'] ?? ''));
        $datos['activo']          = isset($_POST['activo']) ? '1' : '0';

        if ($datos['nombre'] === '' || strlen($datos['nombre']) > 100) {
            $alertas[] = 'El nombre es obligatorio (máximo 100 caracteres)';
        }
        if ($datos['nombre_contacto'] === '' || strlen($datos['nombre_contacto']) > 100) {
            $alertas[] = 'El nombre de contacto es obligatorio (máximo 100 caracteres)';
        }
        if ($datos['telefono'] === '' || strlen($datos['telefono']) > 15) {
            $alertas[] = 'El teléfono es obligatorio (máximo 15 caracteres)';
        }

        if ($datos['correo'] === '' || !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $alertas[] = 'El correo no es válido';
        } elseif (strlen($datos['correo']) > 100) {
            $alertas[] = 'El correo no puede pasar de 100 caracteres';
        } else {
            $otro = Proveedor::where('correo', $datos['correo']);
            if ($otro && (int)$otro->id !== (int)$id_editar) {
                $alertas[] = 'Ya existe un proveedor con ese correo';
            }
        }

        if ($datos['direccion'] === '' || strlen($datos['direccion']) > 150) {
            $alertas[] = 'La dirección es obligatoria (máximo 150 caracteres)';
        }

        if (!in_array(strlen($datos['rfc']), [12, 13])) {
            $alertas[] = 'El RFC es obligatorio y debe tener 12 o 13 caracteres';
        } else {
            $otro = Proveedor::where('rfc', $datos['rfc']);
            if ($otro && (int)$otro->id !== (int)$id_editar) {
                $alertas[] = 'Ya existe un proveedor con ese RFC';
            }
        }

        if (empty($alertas)) {
            try {
                if ($editando) {
                    $proveedor = Proveedor::find($id_editar);
                    $proveedor->sincronizar($datos);
                    $proveedor->actualizar();
                    header('Location: proveedores.php?msg=actualizado');
                    exit;
                }

                $proveedor = new Proveedor($datos);
                $resultado = $proveedor->crear();
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
        'actualizado' => ['green', 'Proveedor actualizado correctamente'],
        'activado'    => ['green', 'Proveedor activado'],
        'desactivado' => ['green', 'Proveedor desactivado'],
        'no_existe'   => ['red',   'El proveedor ya no existe'],
        'error'       => ['red',   'Ocurrió un error'],
    ];
    $msg = $_GET['msg'] ?? '';

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
    <h1><?php echo $id_editar ? 'Editar proveedor' : 'Registrar proveedor'; ?></h1>

    <?php if (isset($mensajes[$msg])): ?>
        <p style="color:<?php echo $mensajes[$msg][0]; ?>;"><?php echo s($mensajes[$msg][1]); ?></p>
    <?php endif; ?>

    <?php foreach ($alertas as $alerta): ?>
        <p style="color:red;"><?php echo s($alerta); ?></p>
    <?php endforeach; ?>

    <?php if ($exito): ?>
        <p style="color:green;">Proveedor guardado correctamente</p>
    <?php endif; ?>

    <form method="POST" action="proveedores.php">
        <?php if ($id_editar): ?>
            <input type="hidden" name="id" value="<?php echo s($id_editar); ?>">
        <?php endif; ?>

        <p>
            <label for="nombre">Nombre</label><br>
            <input type="text" id="nombre" name="nombre" maxlength="100" value="<?php echo s($datos['nombre']); ?>">
        </p>
        <p>
            <label for="nombre_contacto">Nombre de contacto</label><br>
            <input type="text" id="nombre_contacto" name="nombre_contacto" maxlength="100" value="<?php echo s($datos['nombre_contacto']); ?>">
        </p>
        <p>
            <label for="telefono">Teléfono</label><br>
            <input type="tel" id="telefono" name="telefono" maxlength="15" value="<?php echo s($datos['telefono']); ?>">
        </p>
        <p>
            <label for="correo">Correo</label><br>
            <input type="email" id="correo" name="correo" maxlength="100" value="<?php echo s($datos['correo']); ?>">
        </p>
        <p>
            <label for="direccion">Dirección</label><br>
            <input type="text" id="direccion" name="direccion" maxlength="150" size="50" value="<?php echo s($datos['direccion']); ?>">
        </p>
        <p>
            <label for="rfc">RFC (12 o 13 caracteres)</label><br>
            <input type="text" id="rfc" name="rfc" maxlength="13" value="<?php echo s($datos['rfc']); ?>">
        </p>
        <p>
            <label>
                <input type="checkbox" name="activo" value="1" <?php echo $datos['activo'] === '1' ? 'checked' : ''; ?>> Activo
            </label>
        </p>

        <input type="submit" value="<?php echo $id_editar ? 'Actualizar proveedor' : 'Guardar proveedor'; ?>">
        <?php if ($id_editar): ?>
            <a href="proveedores.php">Cancelar</a>
        <?php endif; ?>
    </form>

    <h2>Proveedores registrados</h2>
    <table border="1" cellpadding="4">
        <tr>
            <th>ID</th><th>Nombre</th><th>Contacto</th><th>Teléfono</th>
            <th>Correo</th><th>RFC</th><th>Dirección</th><th>Estado</th><th>Acciones</th>
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
            <td><?php echo $prov->activo ? 'Activo' : 'Inactivo'; ?></td>
            <td>
                <a href="proveedores.php?editar=<?php echo s($prov->id); ?>">Editar</a>

                <form method="POST" action="proveedores.php" style="display:inline;"
                      onsubmit="return confirm('¿Cambiar el estado de este proveedor?');">
                    <input type="hidden" name="accion" value="cambiar_estado">
                    <input type="hidden" name="id" value="<?php echo s($prov->id); ?>">
                    <input type="submit" value="<?php echo $prov->activo ? 'Desactivar' : 'Activar'; ?>">
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
