<?php 

require_once __DIR__ . '/ActiveRecord.php';

class Empleado extends ActiveRecord{

    protected static string $tabla = 'Empleados';
    protected static array $columnasDB = ['id', 'nombre', 'apellido', 'nombre_usuario', 'telefono', 'correo', 'sueldo', 'fecha_ingreso', 'activo', 'password', 'rfc', 'curp', 'id_rol'];

    public $id;
    public $nombre;
    public $apellido;
    public $nombre_usuario;
    public $telefono;
    public $correo;
    public $sueldo;
    public $fecha_ingreso;
    public $activo;
    public $password;
    public $rfc;
    public $curp;
    public $id_rol;
    
    public function __construct($args = []){
        $this->id = $args['id'] ?? 0;
        $this->nombre = $args['nombre'] ?? '';
        $this->apellido = $args['apellido'] ?? '';
        $this->nombre_usuario = $args['nombre_usuario'] ?? '';
        $this->telefono = $args['telefono'] ?? '';
        $this->correo = $args['correo '] ?? '';
        $this->sueldo = $args['sueldo'] ?? '';
        $this->fecha_ingreso = $args['fecha_ingreso'] ?? '';
        $this->activo = $args['activo'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->rfc = $args['rfc'] ?? '';
        $this->curp = $args['curp'] ?? '';
        $this->id_rol = $args['id_rol'] ?? '';
    }

}

?>