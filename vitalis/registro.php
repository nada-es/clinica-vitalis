<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';
$paginaActual = 'registro';

if (estaLogueado()) {
    redirigir('index.php');
}

$errores = [];
$valores = [
    'nombre' => '', 'apellidos' => '', 'email' => '', 'telefono' => '',
    'fecha_nacimiento' => '', 'direccion' => '', 'sexo' => '',
    'usuario' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $valores['nombre'] = limpiar($_POST['nombre'] ?? '');
    $valores['apellidos'] = limpiar($_POST['apellidos'] ?? '');
    $valores['email'] = limpiar($_POST['email'] ?? '');
    $valores['telefono'] = limpiar($_POST['telefono'] ?? '');
    $valores['fecha_nacimiento'] = limpiar($_POST['fecha_nacimiento'] ?? '');
    $valores['direccion'] = limpiar($_POST['direccion'] ?? '');
    $valores['sexo'] = limpiar($_POST['sexo'] ?? '');
    $valores['usuario'] = limpiar($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // --- Validación de campos obligatorios ---
    if ($valores['nombre'] === '') $errores[] = 'El nombre es obligatorio.';
    if ($valores['apellidos'] === '') $errores[] = 'Los apellidos son obligatorios.';
    if ($valores['email'] === '') {
        $errores[] = 'El email es obligatorio.';
    } elseif (!filter_var($valores['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El email introducido no es válido.';
    }
    if ($valores['telefono'] === '') $errores[] = 'El teléfono es obligatorio.';
    if ($valores['fecha_nacimiento'] === '') {
        $errores[] = 'La fecha de nacimiento es obligatoria.';
    } elseif (!DateTime::createFromFormat('Y-m-d', $valores['fecha_nacimiento'])) {
        $errores[] = 'La fecha de nacimiento no es válida.';
    }
    if (!in_array($valores['sexo'], ['Mujer', 'Hombre', 'Otro'], true)) {
        $errores[] = 'Selecciona un sexo válido.';
    }
    if ($valores['usuario'] === '') $errores[] = 'El nombre de usuario es obligatorio.';
    if (strlen($password) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    if ($password !== $password2) $errores[] = 'Las contraseñas no coinciden.';

    // --- Comprobación de duplicados en base de datos ---
    if (empty($errores)) {
        $stmt = mysqli_prepare($conexion, "SELECT idUser FROM users_data WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $valores['email']);
        mysqli_stmt_execute($stmt);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
            $errores[] = 'Ya existe una cuenta registrada con ese email.';
        }
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($conexion, "SELECT idLogin FROM users_login WHERE usuario = ?");
        mysqli_stmt_bind_param($stmt, 's', $valores['usuario']);
        mysqli_stmt_execute($stmt);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
            $errores[] = 'Ese nombre de usuario ya está en uso.';
        }
        mysqli_stmt_close($stmt);
    }

    // --- Inserción si todo es correcto ---
    if (empty($errores)) {
        mysqli_begin_transaction($conexion);
        try {
            $stmt = mysqli_prepare($conexion,
                "INSERT INTO users_data (nombre, apellidos, email, telefono, fecha_nacimiento, direccion, sexo)
                 VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sssssss',
                $valores['nombre'], $valores['apellidos'], $valores['email'], $valores['telefono'],
                $valores['fecha_nacimiento'], $valores['direccion'], $valores['sexo']);
            mysqli_stmt_execute($stmt);
            $idUser = mysqli_insert_id($conexion);
            mysqli_stmt_close($stmt);

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $rol = 'user';
            $stmt = mysqli_prepare($conexion,
                "INSERT INTO users_login (idUser, usuario, password, rol) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'isss', $idUser, $valores['usuario'], $hash, $rol);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            mysqli_commit($conexion);

            setMensaje('exito', '¡Registro completado! Ya puedes iniciar sesión con tu usuario y contraseña.');
            redirigir('login.php');
        } catch (Exception $e) {
            mysqli_rollback($conexion);
            $errores[] = 'No se ha podido completar el registro. Inténtalo de nuevo.';
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
<title>Registro — Clínica Vitalis</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="pagina-cabecera">
    <div class="contenedor">
        <h1>Crea tu cuenta</h1>
        <p>Regístrate para poder solicitar citas y consultar tu perfil.</p>
    </div>
</div>

<section class="seccion">
    <div class="contenedor">
        <div class="tarjeta formulario">
            <?php if (!empty($errores)): ?>
                <div class="alerta alerta-error">
                    <ul style="margin:0; padding-left:18px;">
                        <?php foreach ($errores as $e): ?>
                            <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="registro.php" novalidate>
                <div class="fila-doble">
                    <div class="campo">
                        <label>Nombre <span class="obligatorio">*</span></label>
                        <input type="text" name="nombre" value="<?= $valores['nombre'] ?>" required>
                    </div>
                    <div class="campo">
                        <label>Apellidos <span class="obligatorio">*</span></label>
                        <input type="text" name="apellidos" value="<?= $valores['apellidos'] ?>" required>
                    </div>
                </div>

                <div class="campo">
                    <label>Email <span class="obligatorio">*</span></label>
                    <input type="email" name="email" value="<?= $valores['email'] ?>" required>
                </div>

                <div class="fila-doble">
                    <div class="campo">
                        <label>Teléfono <span class="obligatorio">*</span></label>
                        <input type="text" name="telefono" value="<?= $valores['telefono'] ?>" required>
                    </div>
                    <div class="campo">
                        <label>Fecha de nacimiento <span class="obligatorio">*</span></label>
                        <input type="date" name="fecha_nacimiento" value="<?= $valores['fecha_nacimiento'] ?>" required>
                    </div>
                </div>

                <div class="campo">
                    <label>Dirección</label>
                    <input type="text" name="direccion" value="<?= $valores['direccion'] ?>">
                </div>

                <div class="campo">
                    <label>Sexo <span class="obligatorio">*</span></label>
                    <select name="sexo" required>
                        <option value="">Selecciona una opción</option>
                        <option value="Mujer" <?= $valores['sexo'] === 'Mujer' ? 'selected' : '' ?>>Mujer</option>
                        <option value="Hombre" <?= $valores['sexo'] === 'Hombre' ? 'selected' : '' ?>>Hombre</option>
                        <option value="Otro" <?= $valores['sexo'] === 'Otro' ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>

                <hr style="border:none;border-top:1px solid var(--linea);margin:26px 0;">
                <h3 style="font-size:1.1rem;">Datos de acceso</h3>

                <div class="campo">
                    <label>Usuario <span class="obligatorio">*</span></label>
                    <input type="text" name="usuario" value="<?= $valores['usuario'] ?>" required>
                </div>
                <div class="fila-doble">
                    <div class="campo">
                        <label>Contraseña <span class="obligatorio">*</span></label>
                        <input type="password" name="password" required minlength="6">
                        <small>Mínimo 6 caracteres.</small>
                    </div>
                    <div class="campo">
                        <label>Repite la contraseña <span class="obligatorio">*</span></label>
                        <input type="password" name="password2" required minlength="6">
                    </div>
                </div>

                <button type="submit" class="boton">Crear cuenta</button>
                <p class="form-enlace">¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>.</p>
            </form>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>
