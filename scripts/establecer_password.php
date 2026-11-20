<?php
// Pone (o cambia) la contraseña de un empleado desde la terminal.
// Sirve para el primer administrador, porque los empleados del dump traen una
// contraseña de relleno que no permite entrar.
//
// Uso (desde la raíz del proyecto):
//   php scripts/establecer_password.php lmartinez MiClaveSegura123

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Este script solo se puede ejecutar desde la terminal.');
}

require_once __DIR__ . '/../includes/app.php';

if ($argc !== 3) {
    exit("Uso: php scripts/establecer_password.php <nombre_usuario> <nueva_contraseña>\n");
}

[, $usuario, $password] = $argv;

if (strlen($password) < 8) {
    exit("La contraseña debe tener al menos 8 caracteres.\n");
}

$empleado = Empleado::where('nombre_usuario', $usuario);
if (!$empleado) {
    exit("No existe un empleado con el usuario '{$usuario}'.\n");
}

$empleado->password = password_hash($password, PASSWORD_BCRYPT);
// PostgreSQL devuelve el booleano como true/false; se pasa como '1'/'0' para poder guardarlo de vuelta
$empleado->activo = esVerdadero($empleado->activo) ? '1' : '0';
$empleado->actualizar();

echo "Listo: la contraseña de '{$usuario}' fue actualizada.\n";
