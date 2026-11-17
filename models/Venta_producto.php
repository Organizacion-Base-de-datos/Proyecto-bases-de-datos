<?php 

require_once __DIR__ . '/../includes/app.php';

class Venta_producto extends ActiveRecord{
 
    protected static $tabla = 'Ventas_productos';
    protected static $columnasDB = ['id_venta', 'id_producto'];

    public int $id_venta;
    public int $id_producto;

    public function __construct($args = []){
        $this->id_venta = $args['id_venta'] ?? null;
        $this->id_producto = $args['id_producto'] ?? null;
    }

}