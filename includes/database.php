<?php
    $host = "localhost";
    $port = "5432";
    $dbname = "papeleria";
    $user = "postgres";
    $password = "12345678";

    try {

        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $conexion = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

    } catch (PDOException $e) {
        echo "Error de conexión: " . $e->getMessage();
    }