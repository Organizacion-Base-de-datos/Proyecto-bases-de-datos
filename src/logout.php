<?php
    require_once __DIR__ . '/../includes/app.php';

    cerrarSesionUsuario();
    header('Location: ' . urlSrc('login.php?msg=salio'));
    exit;
