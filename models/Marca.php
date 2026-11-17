<?php 

require_once __DIR__ . '/../includes/app.php';

class Marca extends ActiveRecord{

    protected static string $tabla = 'Marcas';
    protected static array $columnasDB = ['id', 'nombre'];

    public $id;
    public $nombre;

    public function __construct($args = []){
        $this->id = $args['id'] ?? 0;
        $this->nombre = $args['nombre'] ?? '';
    }

}