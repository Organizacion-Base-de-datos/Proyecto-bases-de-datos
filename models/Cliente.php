<?php 

require_once __DIR__ . '/ActiveRecord.php';

class Cliente extends ActiveRecord{

    protected static string $tabla = 'Clientes';
    protected static array $columnasDB = ['id', 'nombre', 'rfc', 'direccion_fiscal'];

    public $id;
    public $nombre;
    public $rfc;
    public $direccion_fiscal;

    public function __construct($args = []){
        $this->id = $args['id'] ?? 0;
        $this->nombre = $args['nombre'] ?? '';
        $this->rfc = $args['rfc'] ?? '';
        $this->direccion_fiscal = $args['direccion_fiscal'] ?? '';
    }

}

?>