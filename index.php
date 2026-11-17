<?php
    require_once __DIR__ . '/includes/app.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

    <h1>[INICIO DE LA PÁGINA]</h1>

    <?php

        $productos = Producto::all();
        debuguear($productos);
        
    ?>

</body>
</html>