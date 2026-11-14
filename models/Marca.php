<?php 

namespace Model;

class Marca extends ActiveRecord{

    protected static $tabla = 'Marcas';
    protected static $columnasDB = ['id', 'nombre'];

    public int $id;
    public $nombre;

    public function __construct($args = []){
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
    }

}