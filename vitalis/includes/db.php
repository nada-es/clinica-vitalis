<?php
/**
 * Conexión a la base de datos MySQL
 * Clínica Vitalis - Trabajo Final PHP
 */

// Railway provide database URL as env var, fall back to localhost for local dev
$dbUrl = getenv('DATABASE_URL') ?: getenv('MYSQL_URL') ?: '';

if ($dbUrl) {
    $parts = parse_url($dbUrl);
    $dbHost = $parts['host'] ?? 'localhost';
    $dbUser = $parts['user'] ?? '';
    $dbPass = $parts['pass'] ?? '';
    $dbName = ltrim($parts['path'] ?? '', '/');
    $dbPort = $parts['port'] ?? 3306;
} else {
    $dbHost = getenv('DB_HOST') ?: 'localhost';
    $dbUser = getenv('DB_USER') ?: 'root';
    $dbPass = getenv('DB_PASS') ?: '';
    $dbName = getenv('DB_NAME') ?: 'clinica_vitalis';
    $dbPort = getenv('DB_PORT') ?: 3306;
}

$conexion = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, (int) $dbPort);

if (!$conexion) {
    die('Error de conexión con la base de datos: ' . mysqli_connect_error());
}

mysqli_set_charset($conexion, 'utf8mb4');
