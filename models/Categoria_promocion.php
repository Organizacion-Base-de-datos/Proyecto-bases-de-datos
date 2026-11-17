<?php 

require_once __DIR__ . '/../includes/app.php';

class Categoria_promocion extends ActiveRecord{
    
    protected static $tabla = 'Categorias_promociones';
    protected static $columnasDB = ['id_categoria', 'id_promocion'];

    public int $id_categoria;
    public int $id_promocion;

    public function __construct($args = []){
        $this->id_categoria = $args['id_categoria'] ?? null;
        $this->id_promocion = $args['id_promocion'] ?? null;
    }

}