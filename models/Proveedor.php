<?php 

require_once __DIR__ . '/../includes/app.php';

class Proveedor extends ActiveRecord{
    
    protected static string $tabla = 'Proveedores';
    protected static array $columnasDB = ['id', 'nombre', 'nombre_contacto', 'telefono', 'correo', 'direccion', 'rfc', 'activo'];

    public $id;
    public $nombre;
    public $nombre_contacto;
    public $telefono;
    public $correo;
    public $direccion;
    public $rfc;
    public $activo;

    public function __construct($args = []){
        $this->id = $args['id'] ?? 0;
        $this->nombre = $args['nombre'] ?? '';
        $this->nombre_contacto = $args['nombre_contacto'] ?? '';
        $this->telefono = $args['telefono'] ?? '';
        $this->correo = $args['correo'] ?? '';
        $this->direccion = $args['direccion'] ?? '';
        $this->rfc = $args['rfc'] ?? '';
        $this->activo = $args['activo'] ?? '';
    }

}