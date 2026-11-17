<?php 

require_once __DIR__ . '/ActiveRecord.php';

class Producto extends ActiveRecord{
    
    protected static string $tabla = 'Productos';
    protected static array $columnasDB = ['id', 'nombre', 'stock', 'precio_costo', 'precio_venta', 'id_marca', 'id_categoria', 'id_proveedor'];

    public $id;
    public $nombre;
    public $stock;
    public $precio_costo;
    public $precio_venta;
    public $id_marca;
    public $id_categoria;
    public $id_proveedor;

    public function __construct($args = []){
        $this->id = $args['id'] ?? 0;
        $this->nombre = $args['nombre'] ?? '';
        $this->stock = $args['stock'] ?? '';
        $this->precio_costo = $args['precio_costo'] ?? '';
        $this->precio_venta = $args['precio_venta'] ?? '';
        $this->id_marca = $args['id_marca'] ?? '';
        $this->id_categoria = $args['id_categoria'] ?? '';
        $this->id_proveedor = $args['id_proveedor'] ?? '';
    }

}

?>