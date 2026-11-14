<?php 

namespace Model;

class Promocion extends ActiveRecord{
    
    protected static $tabla = 'Promociones';
    protected static $columnasDB = ['id', 'nombre', 'tipo', 'descuento', 'descripcion', 'fecha_inicio', 'fecha_fin'];

    public int $id;
    public $nombre;
    public $tipo;
    public $descuento;
    public $descripcion;
    public $fecha_inicio;
    public $fecha_fin;

    public function __construct($args = []){
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->tipo = $args['tipo'] ?? '';
        $this->descuento = $args['descuento'] ?? '';
        $this->descripcion = $args['descripcion'] ?? '';
        $this->fecha_inicio = $args['fecha_inicio'] ?? '';
        $this->fecha_fin = $args['fecha_fin'] ?? '';
    }

}