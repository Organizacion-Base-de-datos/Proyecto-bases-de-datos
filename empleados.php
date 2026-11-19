<?php
    require_once __DIR__ . '/includes/app.php';

    $alertas = [];
    $exito = false;

    $roles = Rol::all();

    $datos = [
        'nombre' => '', 'apellido' => '', 'nombre_usuario' => '', 'telefono' => '',
        'correo' => '', 'sueldo' => '', 'fecha_ingreso' => date('Y-m-d'),
        'rfc' => '', 'curp' => '', 'id_rol' => '', 'activo' => '1',
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $datos['nombre']         = trim($_POST['nombre'] ?? '');
        $datos['apellido']       = trim($_POST['apellido'] ?? '');
        $datos['nombre_usuario'] = trim($_POST['nombre_usuario'] ?? '');
        $datos['telefono']       = trim($_POST['telefono'] ?? '');
        $datos['correo']         = trim($_POST['correo'] ?? '');
        $datos['sueldo']         = trim($_POST['sueldo'] ?? '');
        $datos['fecha_ingreso']  = $_POST['fecha_ingreso'] ?? '';
        $datos['rfc']            = strtoupper(trim($_POST['rfc'] ?? ''));
        $datos['curp']           = strtoupper(trim($_POST['curp'] ?? ''));
        $datos['id_rol']         = $_POST['id_rol'] ?? '';
        $datos['activo']         = isset($_POST['activo']) ? '1' : '0';
        $password                = $_POST['password'] ?? '';

        if ($datos['nombre'] === '' || strlen($datos['nombre']) > 50) {
            $alertas[] = 'El nombre es obligatorio (máximo 50 caracteres)';
        }
        if ($datos['apellido'] === '' || strlen($datos['apellido']) > 50) {
            $alertas[] = 'El apellido es obligatorio (máximo 50 caracteres)';
        }

        if ($datos['nombre_usuario'] === '' || strlen($datos['nombre_usuario']) > 30) {
            $alertas[] = 'El nombre de usuario es obligatorio (máximo 30 caracteres)';
        } elseif (Empleado::where('nombre_usuario', $datos['nombre_usuario'])) {
            $alertas[] = 'Ese nombre de usuario ya está en uso';
        }

        if ($datos['telefono'] === '' || strlen($datos['telefono']) > 15) {
            $alertas[] = 'El teléfono es obligatorio (máximo 15 caracteres)';
        }

        if ($datos['correo'] === '' || !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $alertas[] = 'El correo no es válido';
        } elseif (strlen($datos['correo']) > 100) {
            $alertas[] = 'El correo no puede pasar de 100 caracteres';
        }

        if (!is_numeric($datos['sueldo']) || $datos['sueldo'] < 0 || $datos['sueldo'] > 99999999.99) {
            $alertas[] = 'El sueldo debe ser un número válido';
        }

        if ($datos['fecha_ingreso'] === '') {
            $alertas[] = 'La fecha de ingreso es obligatoria';
        }

        if (strlen($password) < 8) {
            $alertas[] = 'La contraseña debe tener al menos 8 caracteres';
        }

        if (strlen($datos['rfc']) !== 13) {
            $alertas[] = 'El RFC debe tener 13 caracteres';
        } elseif (Empleado::where('rfc', $datos['rfc'])) {
            $alertas[] = 'Ya existe un empleado con ese RFC';
        }

        if (strlen($datos['curp']) !== 18) {
            $alertas[] = 'El CURP debe tener 18 caracteres';
        } elseif (Empleado::where('curp', $datos['curp'])) {
            $alertas[] = 'Ya existe un empleado con ese CURP';
        }

        if (!ctype_digit((string)$datos['id_rol']) || !Rol::find((int)$datos['id_rol'])) {
            $alertas[] = 'Selecciona un rol válido';
        }

        if (empty($alertas)) {
            try {
                $empleado = new Empleado([
                    'nombre'         => $datos['nombre'],
                    'apellido'       => $datos['apellido'],
                    'nombre_usuario' => $datos['nombre_usuario'],
                    'telefono'       => $datos['telefono'],
                    'correo'         => $datos['correo'],
                    'sueldo'         => (float)$datos['sueldo'],
                    'fecha_ingreso'  => $datos['fecha_ingreso'],
                    'activo'         => $datos['activo'],
                    'password'       => password_hash($password, PASSWORD_BCRYPT),
                    'rfc'            => $datos['rfc'],
                    'curp'           => $datos['curp'],
                    'id_rol'         => (int)$datos['id_rol'],
                ]);
                $resultado = $empleado->crear();
                $exito = $resultado['resultado'];

                if ($exito) {
                    $datos = [
                        'nombre' => '', 'apellido' => '', 'nombre_usuario' => '', 'telefono' => '',
                        'correo' => '', 'sueldo' => '', 'fecha_ingreso' => date('Y-m-d'),
                        'rfc' => '', 'curp' => '', 'id_rol' => '', 'activo' => '1',
                    ];
                }
            } catch (PDOException $e) {
                $alertas[] = 'Error al guardar: ' . $e->getMessage();
            }
        }
    }

    $empleados = Empleado::all();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Empleados</title>
</head>
<body>
    <h1>Registrar empleado</h1>

    <?php foreach ($alertas as $alerta): ?>
        <p style="color:red;"><?php echo s($alerta); ?></p>
    <?php endforeach; ?>

    <?php if ($exito): ?>
        <p style="color:green;">Empleado guardado correctamente</p>
    <?php endif; ?>

    <?php if (empty($roles)): ?>
        <p>Primero registra al menos un rol en <a href="rol.php">rol.php</a>.</p>
    <?php else: ?>
    <form method="POST" action="">
        <p>
            <label for="nombre">Nombre</label><br>
            <input type="text" id="nombre" name="nombre" maxlength="50" value="<?php echo s($datos['nombre']); ?>">
        </p>
        <p>
            <label for="apellido">Apellido</label><br>
            <input type="text" id="apellido" name="apellido" maxlength="50" value="<?php echo s($datos['apellido']); ?>">
        </p>
        <p>
            <label for="nombre_usuario">Nombre de usuario</label><br>
            <input type="text" id="nombre_usuario" name="nombre_usuario" maxlength="30" value="<?php echo s($datos['nombre_usuario']); ?>">
        </p>
        <p>
            <label for="password">Contraseña (mínimo 8 caracteres)</label><br>
            <input type="password" id="password" name="password" autocomplete="new-password">
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
            <label for="sueldo">Sueldo</label><br>
            <input type="number" id="sueldo" name="sueldo" step="0.01" min="0" value="<?php echo s($datos['sueldo']); ?>">
        </p>
        <p>
            <label for="fecha_ingreso">Fecha de ingreso</label><br>
            <input type="date" id="fecha_ingreso" name="fecha_ingreso" value="<?php echo s($datos['fecha_ingreso']); ?>">
        </p>
        <p>
            <label for="rfc">RFC (13 caracteres)</label><br>
            <input type="text" id="rfc" name="rfc" maxlength="13" value="<?php echo s($datos['rfc']); ?>">
        </p>
        <p>
            <label for="curp">CURP (18 caracteres)</label><br>
            <input type="text" id="curp" name="curp" maxlength="18" value="<?php echo s($datos['curp']); ?>">
        </p>
        <p>
            <label for="id_rol">Rol</label><br>
            <select id="id_rol" name="id_rol">
                <option value="">-- Selecciona --</option>
                <?php foreach ($roles as $rol): ?>
                    <option value="<?php echo s($rol->id); ?>" <?php echo ((string)$datos['id_rol'] === (string)$rol->id) ? 'selected' : ''; ?>>
                        <?php echo s($rol->nombre); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label>
                <input type="checkbox" name="activo" value="1" <?php echo $datos['activo'] === '1' ? 'checked' : ''; ?>> Activo
            </label>
        </p>
        <input type="submit" value="Guardar empleado">
    </form>
    <?php endif; ?>

    <h2>Empleados registrados</h2>
    <table border="1" cellpadding="4">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Usuario</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Sueldo</th>
                <th>Fecha de ingreso</th>
                <th>RFC</th>
                <th>CURP</th>
                <th>Rol</th>
                <th>Activo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($empleados as $emp): ?>
                <?php $rolEmp = Rol::find($emp->id_rol); ?>
                <tr>
                    <td><?php echo s($emp->id); ?></td>
                    <td><?php echo s($emp->nombre); ?></td>
                    <td><?php echo s($emp->apellido); ?></td>
                    <td><?php echo s($emp->nombre_usuario); ?></td>
                    <td><?php echo s($emp->telefono); ?></td>
                    <td><?php echo s($emp->correo); ?></td>
                    <td><?php echo s($emp->sueldo); ?></td>
                    <td><?php echo s($emp->fecha_ingreso); ?></td>
                    <td><?php echo s($emp->rfc); ?></td>
                    <td><?php echo s($emp->curp); ?></td>
                    <td><?php echo $rolEmp ? s($rolEmp->nombre) : '—'; ?></td>
                    <td><?php echo $emp->activo ? 'Sí' : 'No'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>