<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';
$paginaActual = 'login';

if (estaLogueado()) {
    redirigir('index.php');
}

$error = '';
$usuarioValor = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarioValor = limpiar($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuarioValor === '' || $password === '') {
        $error = 'Debes introducir usuario y contraseña.';
    } else {
        $stmt = mysqli_prepare($conexion,
            "SELECT l.idLogin, l.idUser, l.password, l.rol, d.nombre
             FROM users_login l
             INNER JOIN users_data d ON d.idUser = l.idUser
             WHERE l.usuario = ?");
        mysqli_stmt_bind_param($stmt, 's', $usuarioValor);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $fila = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);

        if (!$fila || !password_verify($password, $fila['password'])) {
            $error = 'Usuario o contraseña incorrectos.';
        } else {
            $_SESSION['idUser'] = $fila['idUser'];
            $_SESSION['usuario'] = $usuarioValor;
            $_SESSION['rol'] = $fila['rol'];
            $_SESSION['nombre'] = $fila['nombre'];

            setMensaje('exito', 'Has iniciado sesión correctamente. ¡Bienvenido/a, ' . $fila['nombre'] . '!');
            redirigir('index.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="author" content="Nada Es Sabti">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Iniciar sesión — Clínica Vitalis</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="pagina-cabecera">
    <div class="contenedor">
        <h1>Iniciar sesión</h1>
        <p>Accede a tu cuenta de paciente o de administración.</p>
    </div>
</div>

<section class="seccion">
    <div class="contenedor">
        <div class="tarjeta formulario">
            <?php if ($error): ?>
                <div class="alerta alerta-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php pintarMensaje(); ?>

            <form method="POST" action="login.php" novalidate>
                <div class="campo">
                    <label>Usuario</label>
                    <input type="text" name="usuario" value="<?= htmlspecialchars($usuarioValor, ENT_QUOTES, 'UTF-8') ?>" required autofocus>
                </div>
                <div class="campo">
                    <label>Contraseña</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="boton">Entrar</button>
                <p class="form-enlace">¿Todavía no tienes cuenta? <a href="registro.php">Regístrate aquí</a>.</p>
            </form>

            <p style="margin-top:22px; font-size:0.8rem; color:var(--verde-700);">
                Demo — admin: <code>admin</code> / <code>admin1234</code> · usuario: <code>usuario1</code> / <code>usuario1234</code>
            </p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>
