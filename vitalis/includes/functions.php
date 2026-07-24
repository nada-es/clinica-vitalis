<?php
/**
 * Funciones auxiliares generales
 * Clínica Vitalis - Trabajo Final PHP
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Limpia una cadena de texto recibida de un formulario */
function limpiar($valor) {
    return htmlspecialchars(trim($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Devuelve true si hay un usuario con sesión iniciada */
function estaLogueado() {
    return isset($_SESSION['idUser']);
}

/** Devuelve el rol del usuario en sesión ('admin', 'user') o null si es visitante */
function rolActual() {
    return $_SESSION['rol'] ?? null;
}

/** Corta la ejecución y redirige a otra página del sitio */
function redirigir($pagina) {
    header('Location: ' . $pagina);
    exit;
}

/** Obliga a que exista sesión iniciada; si no, redirige al login */
function requiereLogin() {
    if (!estaLogueado()) {
        redirigir('login.php');
    }
}

/** Obliga a que el usuario en sesión tenga el rol indicado */
function requiereRol($rolNecesario) {
    requiereLogin();
    if (rolActual() !== $rolNecesario) {
        redirigir('index.php');
    }
}

/** Guarda un mensaje flash (éxito o error) para mostrarlo tras una redirección */
function setMensaje($tipo, $texto) {
    $_SESSION['mensaje'] = ['tipo' => $tipo, 'texto' => $texto];
}

/** Recupera y elimina el mensaje flash almacenado */
function obtenerMensaje() {
    if (!empty($_SESSION['mensaje'])) {
        $m = $_SESSION['mensaje'];
        unset($_SESSION['mensaje']);
        return $m;
    }
    return null;
}

/** Pinta el bloque HTML del mensaje flash, si existe */
function pintarMensaje() {
    $m = obtenerMensaje();
    if ($m) {
        $clase = $m['tipo'] === 'error' ? 'alerta alerta-error' : 'alerta alerta-exito';
        echo '<div class="' . $clase . '">' . htmlspecialchars($m['texto'], ENT_QUOTES, 'UTF-8') . '</div>';
    }
}
