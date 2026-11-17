<?php 

require_once __DIR__ . '/../includes/app.php';

class Venta extends ActiveRecord{
 
    protected static string $tabla = 'Ventas';
    protected static array $columnasDB = ['id', 'fecha', 'total', 'id_cliente', 'id_empleado'];

    public $id;
    public $fecha;
    public $total;
    public $id_cliente;
    public $id_empleado;

    public function __construct($args = []){
        $this->id = $args['id'] ?? 0;
        $this->fecha = $args['fecha'] ?? '';
        $this->total = $args['total'] ?? '';
        $this->id_cliente = $args['id_cliente'] ?? '';
        $this->id_empleado = $args['id_empleado'] ?? '';
    }
    
}