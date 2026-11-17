<?php 

require_once __DIR__ . '/ActiveRecord.php';

class Categoria_promocion extends ActiveRecord{
    
    protected static string $tabla = 'Categorias_promociones';
    protected static array $columnasDB = ['id_categoria', 'id_promocion'];

    public $id_categoria;
    public $id_promocion;

    public function __construct($args = []){
        $this->id_categoria = $args['id_categoria'] ?? 0;
        $this->id_promocion = $args['id_promocion'] ?? 0;
    }

}

?>