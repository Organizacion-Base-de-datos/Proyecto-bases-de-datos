<?php 

namespace Model;

class Cliente extends ActiveRecord{

    protected static $tabla = 'Clientes';
    protected static $columnasDB = ['id', 'nombre', 'rfc', 'direccion_fiscal'];

    public int $id;
    public $nombre;
    public $rfc;
    public $direccion_fiscal;

    public function __construct($args = []){
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->rfc = $args['rfc'] ?? '';
        $this->direccion_fiscal = $args['direccion_fiscal'] ?? '';
    }

}