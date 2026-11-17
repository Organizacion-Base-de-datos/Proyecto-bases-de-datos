<?php

require_once __DIR__ . '/../includes/app.php';

class Categoria extends ActiveRecord{
    
    protected static $tabla = 'categoria';
    protected static $columnasDB = ['id', 'nombre'];

    public int $id;
    public $nombre;

    public function __construct($args = []){
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
    }

}