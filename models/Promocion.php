<?php 

require_once __DIR__ . '/../includes/app.php';

class Promocion extends ActiveRecord{
    
    protected static string $tabla = 'Promociones';
    protected static array $columnasDB = ['id', 'nombre', 'tipo', 'descuento', 'descripcion', 'fecha_inicio', 'fecha_fin'];

    public $id;
    public $nombre;
    public $tipo;
    public $descuento;
    public $descripcion;
    public $fecha_inicio;
    public $fecha_fin;

    public function __construct($args = []){
        $this->id = $args['id'] ?? 0;
        $this->nombre = $args['nombre'] ?? '';
        $this->tipo = $args['tipo'] ?? '';
        $this->descuento = $args['descuento'] ?? '';
        $this->descripcion = $args['descripcion'] ?? '';
        $this->fecha_inicio = $args['fecha_inicio'] ?? '';
        $this->fecha_fin = $args['fecha_fin'] ?? '';
    }

}