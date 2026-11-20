<?php
// Autenticación y permisos por rol.
// Se carga desde includes/app.php (después de funciones.php).

// Módulos del sistema: clave => [nombre en el menú, archivo dentro de src/]
const MODULOS = [
    'ventas'      => ['Ventas',       'create_update/ventas.php'],
    'productos'   => ['Productos',    'create_update/productos.php'],
    'clientes'    => ['Clientes',     'create_update/clientes.php'],
    'proveedores' => ['Proveedores',  'create_update/proveedores.php'],
    'marcas'      => ['Marcas',       'create_update/marcas.php'],
    'categorias'  => ['Categorías',   'create_update/categorias.php'],
    'promociones' => ['Promociones',  'create_update/promociones.php'],
    'empleados'   => ['Empleados',    'create_update/empleados.php'],
    'roles'       => ['Roles',        'create_update/rol.php'],
];

// Qué módulos ve cada rol. La clave es el nombre del rol en minúsculas y sin acentos
// (tal como está en la tabla Rol). '*' significa todos los módulos.
// Si hay que cambiar quién ve qué, se cambia SOLO aquí.
const PERMISOS_POR_ROL = [
    'administrador' => ['*'],
    'gerente'       => ['ventas', 'productos', 'clientes', 'proveedores', 'marcas', 'categorias', 'promociones'],
    'vendedor'      => ['ventas', 'productos', 'clientes'],
    'almacenista'   => ['productos', 'proveedores', 'marcas', 'categorias'],
];

// "Vendedor" / "vendedor " / "VENDEDOR" -> "vendedor"
function normalizarRol(string $nombre): string {
    $nombre = mb_strtolower(trim($nombre), 'UTF-8');
    return strtr($nombre, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u']);
}

// PostgreSQL puede devolver el booleano como true, 't' o '1'
function esVerdadero($valor): bool {
    return $valor === true || $valor === 't' || $valor === '1' || $valor === 1;
}

// URL base de la carpeta src/ (funciona en localhost:8000, en XAMPP con subcarpeta, etc.)
function urlSrc(string $ruta = ''): string {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $pos = strpos($script, '/src/');
    $base = $pos === false ? '' : substr($script, 0, $pos) . '/src';
    return $base . '/' . ltrim($ruta, '/');
}

// Datos del empleado que inició sesión (o null)
function usuarioActual(): ?array {
    iniciarSesion();
    return $_SESSION['usuario'] ?? null;
}

// Lista de claves de módulos que puede usar el usuario actual
function modulosPermitidos(): array {
    $usuario = usuarioActual();
    if (!$usuario) {
        return [];
    }
    $permisos = PERMISOS_POR_ROL[normalizarRol($usuario['rol'])] ?? [];
    if (in_array('*', $permisos, true)) {
        return array_keys(MODULOS);
    }
    return array_values(array_intersect($permisos, array_keys(MODULOS)));
}

function puedeVer(string $modulo): bool {
    return in_array($modulo, modulosPermitidos(), true);
}

// Se llama al inicio de cada página: sin sesión -> login; sin permiso -> inicio.
function requerirAcceso(?string $modulo = null): void {
    if (!usuarioActual()) {
        header('Location: ' . urlSrc('login.php'));
        exit;
    }
    if ($modulo !== null && !puedeVer($modulo)) {
        header('Location: ' . urlSrc('index.php?msg=sin_permiso'));
        exit;
    }
}

// Guarda en la sesión al empleado que acaba de entrar
function iniciarSesionUsuario(Empleado $empleado, string $nombreRol): void {
    iniciarSesion();
    session_regenerate_id(true); // evita fijación de sesión
    $_SESSION['login'] = true;   // lo usa isAuth() de funciones.php
    $_SESSION['usuario'] = [
        'id'      => (int)$empleado->id,
        'nombre'  => trim($empleado->nombre . ' ' . $empleado->apellido),
        'usuario' => $empleado->nombre_usuario,
        'id_rol'  => (int)$empleado->id_rol,
        'rol'     => $nombreRol,
    ];
}

function cerrarSesionUsuario(): void {
    iniciarSesion();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
