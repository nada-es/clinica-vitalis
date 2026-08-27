<?php
/**
 * Conexión a la base de datos MySQL
 * Clínica Vitalis - Trabajo Final PHP
 */

$dbUrl = getenv('DATABASE_URL') ?: getenv('MYSQL_URL') ?: getenv('MYSQL_PUBLIC_URL') ?: '';

if ($dbUrl) {
    $parts = parse_url($dbUrl);
    $dbHost = $parts['host'] ?? 'localhost';
    $dbUser = $parts['user'] ?? '';
    $dbPass = $parts['pass'] ?? '';
    $dbName = ltrim($parts['path'] ?? '', '/');
    $dbPort = $parts['port'] ?? 3306;
} else {
    $dbHost = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
    $dbUser = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
    $dbPass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '';
    $dbName = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'clinica_vitalis';
    $dbPort = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306;
}

$conexion = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, (int) $dbPort);

if (!$conexion) {
    die('Error de conexión con la base de datos: ' . mysqli_connect_error());
}

mysqli_set_charset($conexion, 'utf8mb4');
