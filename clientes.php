<?php
    require_once __DIR__ . '/includes/app.php';

    $alertas = [];
    $exito = false;

    $datos = ['nombre' => '', 'rfc' => '', 'direccion_fiscal' => ''];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        } elseif (Cliente::where('rfc', $datos['rfc'])) {
            $alertas[] = 'Ya existe un cliente con ese RFC';
        }

        if ($datos['direccion_fiscal'] === '') {
            $alertas[] = 'La dirección fiscal es obligatoria';
        } elseif (strlen($datos['direccion_fiscal']) > 150) {
            $alertas[] = 'La dirección no puede pasar de 150 caracteres';
        }

        if (empty($alertas)) {
            try {
                $cliente = new Cliente([
                    'nombre'           => $datos['nombre'],
                    'rfc'              => $datos['rfc'],
                    'direccion_fiscal' => $datos['direccion_fiscal'],
                ]);
                $resultado = $cliente->crear();
                $exito = $resultado['resultado'];

                if ($exito) {
                    $datos = ['nombre' => '', 'rfc' => '', 'direccion_fiscal' => ''];
                }
            } catch (PDOException $e) {
                $alertas[] = 'Error al guardar: ' . $e->getMessage();
            }
        }
    }

    $clientes = Cliente::all();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes</title>
</head>
<body>
    <h1>Registrar cliente</h1>

    <?php foreach ($alertas as $alerta): ?>
        <p style="color:red;"><?php echo s($alerta); ?></p>
    <?php endforeach; ?>

    <?php if ($exito): ?>
        <p style="color:green;">Cliente guardado correctamente</p>
    <?php endif; ?>

    <form method="POST" action="">
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
        <input type="submit" value="Guardar cliente">
    </form>

    <h2>Clientes registrados</h2>
    <table border="1" cellpadding="4">
        <tr><th>ID</th><th>Nombre</th><th>RFC</th><th>Dirección fiscal</th></tr>
        <?php foreach ($clientes as $c): ?>
            <tr>
                <td><?php echo s($c->id); ?></td>
                <td><?php echo s($c->nombre); ?></td>
                <td><?php echo s($c->rfc); ?></td>
                <td><?php echo s($c->direccion_fiscal); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>