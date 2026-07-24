<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';
$paginaActual = 'perfil';

requiereLogin();
$idUser = $_SESSION['idUser'];

// Datos actuales del usuario
$stmt = mysqli_prepare($conexion,
    "SELECT d.nombre, d.apellidos, d.email, d.telefono, d.fecha_nacimiento, d.direccion, d.sexo, l.usuario
     FROM users_data d INNER JOIN users_login l ON l.idUser = d.idUser
     WHERE d.idUser = ?");
mysqli_stmt_bind_param($stmt, 'i', $idUser);
mysqli_stmt_execute($stmt);
$datos = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$errores = [];
$exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['accion']) && $_POST['accion'] === 'datos') {
        $nombre = limpiar($_POST['nombre'] ?? '');
        $apellidos = limpiar($_POST['apellidos'] ?? '');
        $email = limpiar($_POST['email'] ?? '');
        $telefono = limpiar($_POST['telefono'] ?? '');
        $fecha_nacimiento = limpiar($_POST['fecha_nacimiento'] ?? '');
        $direccion = limpiar($_POST['direccion'] ?? '');
        $sexo = limpiar($_POST['sexo'] ?? '');

        if ($nombre === '') $errores[] = 'El nombre es obligatorio.';
        if ($apellidos === '') $errores[] = 'Los apellidos son obligatorios.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'El email no es válido.';
        if ($telefono === '') $errores[] = 'El teléfono es obligatorio.';
        if ($fecha_nacimiento === '') $errores[] = 'La fecha de nacimiento es obligatoria.';

        if (empty($errores)) {
            $stmt = mysqli_prepare($conexion, "SELECT idUser FROM users_data WHERE email = ? AND idUser <> ?");
            mysqli_stmt_bind_param($stmt, 'si', $email, $idUser);
            mysqli_stmt_execute($stmt);
            if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
                $errores[] = 'Ese email ya está en uso por otra cuenta.';
            }
            mysqli_stmt_close($stmt);
        }

        if (empty($errores)) {
            $stmt = mysqli_prepare($conexion,
                "UPDATE users_data SET nombre=?, apellidos=?, email=?, telefono=?, fecha_nacimiento=?, direccion=?, sexo=? WHERE idUser=?");
            mysqli_stmt_bind_param($stmt, 'sssssssi', $nombre, $apellidos, $email, $telefono, $fecha_nacimiento, $direccion, $sexo, $idUser);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $_SESSION['nombre'] = $nombre;
            setMensaje('exito', 'Tus datos se han actualizado correctamente.');
            redirigir('perfil.php');
        } else {
            $datos = compact('nombre', 'apellidos', 'email', 'telefono', 'fecha_nacimiento', 'direccion', 'sexo') + ['usuario' => $datos['usuario']];
        }
    }

    if (isset($_POST['accion']) && $_POST['accion'] === 'password') {
        $actual = $_POST['password_actual'] ?? '';
        $nueva = $_POST['password_nueva'] ?? '';
        $nueva2 = $_POST['password_nueva2'] ?? '';

        $stmt = mysqli_prepare($conexion, "SELECT password FROM users_login WHERE idUser = ?");
        mysqli_stmt_bind_param($stmt, 'i', $idUser);
        mysqli_stmt_execute($stmt);
        $filaPass = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!password_verify($actual, $filaPass['password'])) {
            $errores[] = 'La contraseña actual no es correcta.';
        } elseif (strlen($nueva) < 6) {
            $errores[] = 'La nueva contraseña debe tener al menos 6 caracteres.';
        } elseif ($nueva !== $nueva2) {
            $errores[] = 'Las contraseñas nuevas no coinciden.';
        } else {
            $hash = password_hash($nueva, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conexion, "UPDATE users_login SET password=? WHERE idUser=?");
            mysqli_stmt_bind_param($stmt, 'si', $hash, $idUser);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            setMensaje('exito', 'Contraseña actualizada correctamente.');
            redirigir('perfil.php');
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
<title>Mi perfil — Clínica Vitalis</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="pagina-cabecera">
    <div class="contenedor">
        <h1>Mi perfil</h1>
        <p>Consulta y actualiza tus datos personales.</p>
    </div>
</div>

<section class="seccion">
    <div class="contenedor rejilla-2">
        <div class="tarjeta">
            <?php if (!empty($errores)): ?>
                <div class="alerta alerta-error">
                    <ul style="margin:0; padding-left:18px;">
                        <?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php pintarMensaje(); ?>

            <h3>Datos personales</h3>
            <form method="POST" action="perfil.php" class="formulario">
                <input type="hidden" name="accion" value="datos">

                <div class="campo">
                    <label>Usuario</label>
                    <input type="text" value="<?= htmlspecialchars($datos['usuario'], ENT_QUOTES, 'UTF-8') ?>" disabled>
                    <small>El nombre de usuario no se puede modificar.</small>
                </div>

                <div class="fila-doble">
                    <div class="campo">
                        <label>Nombre <span class="obligatorio">*</span></label>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($datos['nombre'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="campo">
                        <label>Apellidos <span class="obligatorio">*</span></label>
                        <input type="text" name="apellidos" value="<?= htmlspecialchars($datos['apellidos'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>

                <div class="campo">
                    <label>Email <span class="obligatorio">*</span></label>
                    <input type="email" name="email" value="<?= htmlspecialchars($datos['email'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="fila-doble">
                    <div class="campo">
                        <label>Teléfono <span class="obligatorio">*</span></label>
                        <input type="text" name="telefono" value="<?= htmlspecialchars($datos['telefono'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="campo">
                        <label>Fecha de nacimiento <span class="obligatorio">*</span></label>
                        <input type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($datos['fecha_nacimiento'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>

                <div class="campo">
                    <label>Dirección</label>
                    <input type="text" name="direccion" value="<?= htmlspecialchars($datos['direccion'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="campo">
                    <label>Sexo</label>
                    <select name="sexo">
                        <option value="Mujer" <?= $datos['sexo'] === 'Mujer' ? 'selected' : '' ?>>Mujer</option>
                        <option value="Hombre" <?= $datos['sexo'] === 'Hombre' ? 'selected' : '' ?>>Hombre</option>
                        <option value="Otro" <?= $datos['sexo'] === 'Otro' ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>

                <button type="submit" class="boton">Guardar cambios</button>
            </form>
        </div>

        <div class="tarjeta">
            <h3>Cambiar contraseña</h3>
            <form method="POST" action="perfil.php" class="formulario">
                <input type="hidden" name="accion" value="password">
                <div class="campo">
                    <label>Contraseña actual</label>
                    <input type="password" name="password_actual" required>
                </div>
                <div class="campo">
                    <label>Nueva contraseña</label>
                    <input type="password" name="password_nueva" required minlength="6">
                </div>
                <div class="campo">
                    <label>Repite la nueva contraseña</label>
                    <input type="password" name="password_nueva2" required minlength="6">
                </div>
                <button type="submit" class="boton boton-secundario">Actualizar contraseña</button>
            </form>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>
