<?php

class ActiveRecord
{
    protected static ?PDO $db = null;
    protected static string $tabla = '';
    protected static array $columnasDB = [];

    protected static array $alertas = [];

    // Definir la conexión a la BD - includes/database.php
    public static function setDB(PDO $database): void
    {
        self::$db = $database;
    }

    public static function setAlerta(string $tipo, string $mensaje): void
    {
        static::$alertas[$tipo][] = $mensaje;
    }

    // Validación
    public static function getAlertas(): array
    {
        return static::$alertas;
    }

    public function validar(): array
    {
        static::$alertas = [];
        return static::$alertas;
    }

    // Registros - CRUD
    public function guardar()
    {
        if (!is_null($this->id)) {
            return $this->actualizar();
        } else {
            return $this->crear();
        }
    }

    public static function all(): array
    {
        $query = "SELECT * FROM " . static::$tabla;
        return self::consultarSQL($query);
    }

    // Busca un registro por su id
    public static function find($id)
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE id = :id LIMIT 1";
        $stmt = self::$db->prepare($query);
        $stmt->execute([':id' => $id]);

        $registro = $stmt->fetch(PDO::FETCH_ASSOC);
        return $registro ? static::crearObjeto($registro) : null;
    }

    // Obtener Registro
    public static function get(int $limite)
    {
        $query = "SELECT * FROM " . static::$tabla . " LIMIT :limite";
        $stmt = self::$db->prepare($query);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        $registro = $stmt->fetch(PDO::FETCH_ASSOC);
        return $registro ? static::crearObjeto($registro) : null;
    }

    public static function where(string $columna, $valor)
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE {$columna} = :valor LIMIT 1";
        $stmt = self::$db->prepare($query);
        $stmt->execute([':valor' => $valor]);

        $registro = $stmt->fetch(PDO::FETCH_ASSOC);
        return $registro ? static::crearObjeto($registro) : null;
    }

    public static function belongsTo(string $columna, $valor): array
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE {$columna} = :valor";
        $stmt = self::$db->prepare($query);
        $stmt->execute([':valor' => $valor]);

        $array = [];
        while ($registro = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $array[] = static::crearObjeto($registro);
        }
        return $array;
    }

    // SQL para Consultas Avanzadas
    public static function SQL(string $consulta): array
    {
        return self::consultarSQL($consulta);
    }

    // Crea un nuevo registro
    public function crear(): array
    {
        $atributos = $this->atributos();
        $columnas = array_keys($atributos);
        $placeholders = array_map(fn($col) => ":{$col}", $columnas);

        $query = "INSERT INTO " . static::$tabla . " (" . join(', ', $columnas) . ") ";
        $query .= "VALUES (" . join(', ', $placeholders) . ")";

        $stmt = self::$db->prepare($query);

        foreach ($atributos as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        $resultado = $stmt->execute();

        return [
            'resultado' => $resultado,
            'id' => self::$db->lastInsertId()
        ];
    }

    // Actualiza un registro existente
    public function actualizar(): bool
    {
        $atributos = $this->atributos();

        $valores = [];
        foreach ($atributos as $key => $value) {
            $valores[] = "{$key} = :{$key}";
        }

        $query = "UPDATE " . static::$tabla . " SET " . join(', ', $valores);
        $query .= " WHERE id = :id";

        $stmt = self::$db->prepare($query);

        foreach ($atributos as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        $stmt->bindValue(':id', $this->id);

        return $stmt->execute();
    }

    public function eliminar(): bool
    {
        $query = "DELETE FROM " . static::$tabla . " WHERE id = :id";
        $stmt = self::$db->prepare($query);
        $stmt->bindValue(':id', $this->id);

        return $stmt->execute();
    }

    public static function consultarSQL(string $query): array
    {
        $resultado = self::$db->query($query);

        $array = [];
        while ($registro = $resultado->fetch(PDO::FETCH_ASSOC)) {
            $array[] = static::crearObjeto($registro);
        }

        $resultado->closeCursor();

        return $array;
    }

    protected static function crearObjeto(array $registro): static
    {
        $objeto = new static;

        foreach ($registro as $key => $value) {
            if (property_exists($objeto, $key)) {
                $objeto->$key = $value;
            }
        }

        return $objeto;
    }

    public function atributos(): array
    {
        $atributos = [];
        foreach (static::$columnasDB as $columna) {
            if ($columna === 'id') continue;
            $atributos[$columna] = $this->$columna;
        }
        return $atributos;
    }

    public function sincronizar(array $args = []): void
    {
        foreach ($args as $key => $value) {
            if (property_exists($this, $key) && !is_null($value)) {
                $this->$key = $value;
            }
        }
    }
}

?>