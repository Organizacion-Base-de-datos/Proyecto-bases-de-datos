<?php

function debuguear($variable) : string {
    echo "<pre>";
        var_dump($variable);
    echo "</pre>";
    exit;
}

// Escapa / Sanitizar el HTML
function s($html) : string {
    $s = htmlspecialchars($html);
    return $s;
}

// Función que revisa que el usuario este autenticado
function isAuth() : void {
    if(!isset($_SESSION['login'])) {
        header('Location: /');
        exit;
    }
}

// ? inicia la sesión para usar $_SESSION
function iniciarSesion() : void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}