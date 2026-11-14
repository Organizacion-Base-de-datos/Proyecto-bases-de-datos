<?php 

namespace Model;

class Rol extends ActiveRecord{
    
    protected static $tabla = 'Rol';
    protected static $columnasDB = ['id', 'nombre'];

    public int $id;
    public $nombre;

    public function __construct($args = []){
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
    }
    
}