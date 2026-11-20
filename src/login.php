<?php
    require_once __DIR__ . '/../includes/app.php';

    // Si ya inició sesión, directo al inicio
    if (usuarioActual()) {
        header('Location: ' . urlSrc('index.php'));
        exit;
    }

    $error = '';
    $usuario = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario  = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($usuario === '' || $password === '') {
            $error = 'Escribe tu usuario y tu contraseña';
        } else {
            $empleado = Empleado::where('nombre_usuario', $usuario);

            // password_verify compara contra el hash guardado; nunca se compara texto plano
            $passwordOk = $empleado && password_verify($password, (string)$empleado->password);

            if (!$passwordOk) {
                $error = 'Usuario o contraseña incorrectos';
            } elseif (!esVerdadero($empleado->activo)) {
                $error = 'Tu cuenta está desactivada. Habla con el administrador';
            } else {
                $rol = Rol::find((int)$empleado->id_rol);
                if (!$rol || !isset(PERMISOS_POR_ROL[normalizarRol($rol->nombre)])) {
                    $error = 'Tu rol no tiene acceso al sistema. Habla con el administrador';
                } else {
                    iniciarSesionUsuario($empleado, $rol->nombre);
                    header('Location: ' . urlSrc('index.php'));
                    exit;
                }
            }
        }
    }

    $msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · Papelería</title>
    <link rel="stylesheet" href="<?php echo s(urlSrc('css/estilos.css')); ?>">
</head>
<body>
    <div class="login-fondo">
        <form class="login-caja" method="POST" action="login.php">
            <h1>Papelería</h1>
            <p class="sub">Acceso para empleados</p>

            <?php if ($error !== ''): ?>
                <p class="aviso aviso-error" role="alert"><?php echo s($error); ?></p>
            <?php elseif ($msg === 'salio'): ?>
                <p class="aviso aviso-ok">Cerraste sesión correctamente</p>
            <?php endif; ?>

            <div class="campo">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" maxlength="30" autocomplete="username" autofocus
                       value="<?php echo s($usuario); ?>">
            </div>

            <div class="campo">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-ancho">Entrar</button>
        </form>
    </div>
</body>
</html>
