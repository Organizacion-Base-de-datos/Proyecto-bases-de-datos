<?php 

require 'database.php';
require 'funciones.php';
require_once __DIR__ . '/../models/ActiveRecord.php';
require_once __DIR__ . '/../models/Empleado.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/Categoria_promocion.php';
require_once __DIR__ . '/../models/Marca.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Promocion.php';
require_once __DIR__ . '/../models/Proveedor.php';
require_once __DIR__ . '/../models/Rol.php';
require_once __DIR__ . '/../models/Venta_producto.php';
require_once __DIR__ . '/../models/Venta.php';

$db = conectarDB();

ActiveRecord::setDB($db);

?>