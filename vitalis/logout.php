<?php
require_once 'includes/functions.php';

$_SESSION = [];
session_destroy();

session_start();
setMensaje('exito', 'Has cerrado sesión correctamente.');
redirigir('index.php');
