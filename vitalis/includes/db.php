<?php
/**
 * Conexión a la base de datos MySQL
 * Clínica Vitalis - Trabajo Final PHP
 *
 * IMPORTANTE: si subes el proyecto a un hosting, cambia estos datos
 * por los que te facilite tu proveedor (cPanel, InfinityFree, etc.)
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'clinica_vitalis');

$conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conexion) {
    die('Error de conexión con la base de datos: ' . mysqli_connect_error());
}

mysqli_set_charset($conexion, 'utf8mb4');
