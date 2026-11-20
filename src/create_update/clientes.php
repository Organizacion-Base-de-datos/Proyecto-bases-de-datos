<?php
    require_once __DIR__ . '/../../includes/app.php';
    requerirAcceso('clientes');

    $alertas = [];
    $exito = false;

    $vacio = ['nombre' => '', 'rfc' => '', 'direccion_fiscal' => ''];
    $datos = $vacio;
    $id_editar = null;

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['editar'])) {
        $cli = ctype_digit((string)$_GET['editar']) ? Cliente::find((int)$_GET['editar']) : null;
        if ($cli) {
            $id_editar = (int)$cli->id;
            $datos = [
                'nombre'           => $cli->nombre,
                'rfc'              => $cli->rfc,
                'direccion_fiscal' => $cli->direccion_fiscal,
            ];
        } else {
            $alertas[] = 'El cliente que quieres editar no existe';
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $idPost   = $_POST['id'] ?? '';
        $editando = $idPost !== '';

        if ($editando) {
            if (!ctype_digit((string)$idPost) || !Cliente::find((int)$idPost)) {
                $alertas[] = 'El cliente que intentas actualizar no existe';
            } else {
                $id_editar = (int)$idPost;
            }
        }

        $datos['nombre']           = trim($_POST['nombre'] ?? '');
        $datos['rfc']              = strtoupper(trim($_POST['rfc'] ?? ''));
        $datos['direccion_fiscal'] = trim($_POST['direccion_fiscal'] ?? '');

        if ($datos['nombre'] === '') {
            $alertas[] = 'El nombre del cliente es obligatorio';
        } elseif (strlen($datos['nombre']) > 100) {
            $alertas[] = 'El nombre no puede pasar de 100 caracteres';
        }

        if ($datos['rfc'] === '') {
            $alertas[] = 'El RFC no puede estar vacío';
        } elseif (!in_array(strlen($datos['rfc']), [12, 13])) {
            $alertas[] = 'El RFC debe tener 12 o 13 caracteres';
        } else {
            $otro = Cliente::where('rfc', $datos['rfc']);
            if ($otro && (int)$otro->id !== (int)$id_editar) {
                $alertas[] = 'Ya existe un cliente con ese RFC';
            }
        }

        if ($datos['direccion_fiscal'] === '') {
            $alertas[] = 'La dirección fiscal es obligatoria';
        } elseif (strlen($datos['direccion_fiscal']) > 150) {
            $alertas[] = 'La dirección no puede pasar de 150 caracteres';
        }

        if (empty($alertas)) {
            try {
                if ($editando) {
                    $cliente = Cliente::find($id_editar);
                    $cliente->sincronizar($datos);
                    $cliente->actualizar();
                    header('Location: clientes.php?msg=actualizado');
                    exit;
                }

                $cliente = new Cliente($datos);
                $resultado = $cliente->crear();
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
        'actualizado' => ['green', 'Cliente actualizado correctamente'],
        'eliminado'   => ['green', 'Cliente eliminado correctamente'],
        'en_uso'      => ['red',   'No se puede eliminar: el cliente tiene ventas registradas'],
        'no_existe'   => ['red',   'El registro ya no existe'],
        'error'       => ['red',   'Ocurrió un error'],
    ];
    $msg = $_GET['msg'] ?? '';

    $clientes = Cliente::all();
?>
<?php require_once __DIR__ . '/../../views/header.php'; ?>

    <div class="encabezado-pagina">
        <h1>Clientes</h1>
        <button type="button" class="btn" id="btn-nuevo" aria-controls="panel-form">+ Nuevo</button>
    </div>

    <?php if (isset($mensajes[$msg])): ?>
        <p class="aviso aviso-<?php echo $mensajes[$msg][0] === 'green' ? 'ok' : 'error'; ?>"><?php echo s($mensajes[$msg][1]); ?></p>
    <?php endif; ?>

    <?php foreach ($alertas as $alerta): ?>
        <p class="aviso aviso-error aviso-form"><?php echo s($alerta); ?></p>
    <?php endforeach; ?>

    <?php if ($exito): ?>
        <p class="aviso aviso-ok">Cliente guardado correctamente</p>
    <?php endif; ?>

    <section class="tarjeta panel-form" id="panel-form">
        <h2><?php echo $id_editar ? 'Editar cliente' : 'Registrar cliente'; ?></h2>

    <form method="POST" action="clientes.php" class="form-registro">
        <?php if ($id_editar): ?>
            <input type="hidden" name="id" value="<?php echo s($id_editar); ?>">
        <?php endif; ?>

        <p>
            <label for="nombre">Nombre</label><br>
            <input type="text" id="nombre" name="nombre" maxlength="100" value="<?php echo s($datos['nombre']); ?>">
        </p>
        <p>
            <label for="rfc">RFC (12 o 13 caracteres)</label><br>
            <input type="text" id="rfc" name="rfc" maxlength="13" value="<?php echo s($datos['rfc']); ?>">
        </p>
        <p>
            <label for="direccion_fiscal">Dirección fiscal</label><br>
            <input type="text" id="direccion_fiscal" name="direccion_fiscal" maxlength="150" size="50" value="<?php echo s($datos['direccion_fiscal']); ?>">
        </p>

        <span class="acciones-form"><button type="submit" class="btn"><?php echo $id_editar ? 'Actualizar cliente' : 'Guardar cliente'; ?></button>
        <?php if ($id_editar): ?>
            <a href="clientes.php" class="btn btn-sec">Cancelar</a>
        <?php endif; ?></span>
    </form>

    </section>

    <section class="tarjeta">
        <h2>Clientes registrados</h2>
    <div class="tabla-scroll">
    <table class="tabla">
        <tr><th>ID</th><th>Nombre</th><th>RFC</th><th>Dirección fiscal</th><th>Acciones</th></tr>
        <?php foreach ($clientes as $c): ?>
            <tr>
                <td><?php echo s($c->id); ?></td>
                <td><?php echo s($c->nombre); ?></td>
                <td><?php echo s($c->rfc); ?></td>
                <td><?php echo s($c->direccion_fiscal); ?></td>
                <td>
                    <a href="clientes.php?editar=<?php echo s($c->id); ?>" class="btn btn-sec btn-sm">Editar</a>

                    <form method="POST" action="<?php echo s(urlSrc('delete/eliminar.php')); ?>" style="display:inline;"
                          onsubmit="return confirm('¿Seguro que quieres eliminar este cliente?');">
                        <input type="hidden" name="tipo" value="clientes">
                        <input type="hidden" name="id" value="<?php echo s($c->id); ?>">
                        <button type="submit" class="btn btn-peligro btn-sm">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    </div>
    </section>
<?php require_once __DIR__ . '/../../views/footer.php'; ?>
