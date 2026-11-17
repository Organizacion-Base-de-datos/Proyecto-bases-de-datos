<?php 

require_once __DIR__ . '/ActiveRecord.php';

class Venta_producto extends ActiveRecord{
 
    protected static string $tabla = 'Ventas_productos';
    protected static array $columnasDB = ['id_venta', 'id_producto'];

    public $id_venta;
    public $id_producto;

    public function __construct($args = []){
        $this->id_venta = $args['id_venta'] ?? 0;
        $this->id_producto = $args['id_producto'] ?? 0;
    }

}

?>