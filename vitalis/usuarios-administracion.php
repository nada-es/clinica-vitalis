<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';
$paginaActual = 'usuarios-administracion';

requiereRol('admin');
$errores = [];

// --- Crear usuario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear') {
    $nombre = limpiar($_POST['nombre'] ?? '');
    $apellidos = limpiar($_POST['apellidos'] ?? '');
    $email = limpiar($_POST['email'] ?? '');
    $telefono = limpiar($_POST['telefono'] ?? '');
    $fecha_nacimiento = limpiar($_POST['fecha_nacimiento'] ?? '');
    $direccion = limpiar($_POST['direccion'] ?? '');
    $sexo = limpiar($_POST['sexo'] ?? '');
    $usuario = limpiar($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    $rol = limpiar($_POST['rol'] ?? '');

    if ($nombre === '') $errores[] = 'El nombre es obligatorio.';
    if ($apellidos === '') $errores[] = 'Los apellidos son obligatorios.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'El email no es válido.';
    if ($telefono === '') $errores[] = 'El teléfono es obligatorio.';
    if ($fecha_nacimiento === '') $errores[] = 'La fecha de nacimiento es obligatoria.';
    if ($usuario === '') $errores[] = 'El usuario es obligatorio.';
    if (strlen($password) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    if (!in_array($rol, ['admin', 'user'], true)) $errores[] = 'El rol no es válido.';

    if (empty($errores)) {
        $stmt = mysqli_prepare($conexion, "SELECT idUser FROM users_data WHERE email=?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) $errores[] = 'Ya existe un usuario con ese email.';
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($conexion, "SELECT idLogin FROM users_login WHERE usuario=?");
        mysqli_stmt_bind_param($stmt, 's', $usuario);
        mysqli_stmt_execute($stmt);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) $errores[] = 'Ese nombre de usuario ya está en uso.';
        mysqli_stmt_close($stmt);
    }

    if (empty($errores)) {
        mysqli_begin_transaction($conexion);
        try {
            $stmt = mysqli_prepare($conexion,
                "INSERT INTO users_data (nombre, apellidos, email, telefono, fecha_nacimiento, direccion, sexo) VALUES (?,?,?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, 'sssssss', $nombre, $apellidos, $email, $telefono, $fecha_nacimiento, $direccion, $sexo);
            mysqli_stmt_execute($stmt);
            $idUser = mysqli_insert_id($conexion);
            mysqli_stmt_close($stmt);

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conexion, "INSERT INTO users_login (idUser, usuario, password, rol) VALUES (?,?,?,?)");
            mysqli_stmt_bind_param($stmt, 'isss', $idUser, $usuario, $hash, $rol);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            mysqli_commit($conexion);
            setMensaje('exito', 'Usuario creado correctamente.');
            redirigir('usuarios-administracion.php');
        } catch (Exception $e) {
            mysqli_rollback($conexion);
            $errores[] = 'No se ha podido crear el usuario.';
        }
    }
}

// --- Editar usuario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'editar') {
    $idUser = (int) ($_POST['idUser'] ?? 0);
    $nombre = limpiar($_POST['nombre'] ?? '');
    $apellidos = limpiar($_POST['apellidos'] ?? '');
    $email = limpiar($_POST['email'] ?? '');
    $telefono = limpiar($_POST['telefono'] ?? '');
    $fecha_nacimiento = limpiar($_POST['fecha_nacimiento'] ?? '');
    $direccion = limpiar($_POST['direccion'] ?? '');
    $sexo = limpiar($_POST['sexo'] ?? '');
    $rol = limpiar($_POST['rol'] ?? '');
    $nuevaPassword = $_POST['password'] ?? '';

    if ($nombre === '' || $apellidos === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $telefono === '' || $fecha_nacimiento === '') {
        $errores[] = 'Revisa los campos obligatorios del usuario a editar.';
    }

    if (empty($errores)) {
        $stmt = mysqli_prepare($conexion, "UPDATE users_data SET nombre=?, apellidos=?, email=?, telefono=?, fecha_nacimiento=?, direccion=?, sexo=? WHERE idUser=?");
        mysqli_stmt_bind_param($stmt, 'sssssssi', $nombre, $apellidos, $email, $telefono, $fecha_nacimiento, $direccion, $sexo, $idUser);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($conexion, "UPDATE users_login SET rol=? WHERE idUser=?");
        mysqli_stmt_bind_param($stmt, 'si', $rol, $idUser);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if (strlen($nuevaPassword) >= 6) {
            $hash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conexion, "UPDATE users_login SET password=? WHERE idUser=?");
            mysqli_stmt_bind_param($stmt, 'si', $hash, $idUser);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        setMensaje('exito', 'Usuario actualizado correctamente.');
        redirigir('usuarios-administracion.php');
    }
}

// --- Borrar usuario ---
if (isset($_GET['borrar'])) {
    $idUser = (int) $_GET['borrar'];
    if ($idUser === (int) $_SESSION['idUser']) {
        setMensaje('error', 'No puedes borrar tu propia cuenta mientras tienes la sesión iniciada.');
    } else {
        $stmt = mysqli_prepare($conexion, "DELETE FROM users_data WHERE idUser=?");
        mysqli_stmt_bind_param($stmt, 'i', $idUser);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        setMensaje('exito', 'Usuario eliminado correctamente.');
    }
    redirigir('usuarios-administracion.php');
}

$usuarios = mysqli_query($conexion,
    "SELECT d.idUser, d.nombre, d.apellidos, d.email, d.telefono, d.fecha_nacimiento, d.direccion, d.sexo, l.usuario, l.rol
     FROM users_data d INNER JOIN users_login l ON l.idUser = d.idUser
     ORDER BY d.idUser DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="author" content="Nada Es Sabti">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de usuarios — Clínica Vitalis</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="pagina-cabecera">
    <div class="contenedor">
        <h1>Gestión de usuarios</h1>
        <p>Crea, modifica o elimina las cuentas de pacientes y administradores.</p>
    </div>
</div>

<section class="seccion">
    <div class="contenedor">
        <?php pintarMensaje(); ?>
        <?php if (!empty($errores)): ?>
            <div class="alerta alerta-error">
                <ul style="margin:0; padding-left:18px;">
                    <?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="panel-cabecera">
            <h3 style="margin:0;">Usuarios registrados</h3>
            <button class="boton" onclick="document.getElementById('crearUsuario').showModal()">+ Nuevo usuario</button>
        </div>

        <div style="overflow-x:auto;">
        <table class="tabla-admin">
            <thead>
                <tr><th>Usuario</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php while ($u = mysqli_fetch_assoc($usuarios)): ?>
                <tr>
                    <td><?= htmlspecialchars($u['usuario'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellidos'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><span class="badge <?= $u['rol'] === 'admin' ? 'badge-admin' : 'badge-user' ?>"><?= $u['rol'] ?></span></td>
                    <td class="acciones-tabla">
                        <button class="boton boton-secundario boton-pequeno" onclick="document.getElementById('editar-<?= $u['idUser'] ?>').showModal()">Editar</button>
                        <a class="boton boton-peligro boton-pequeno" href="usuarios-administracion.php?borrar=<?= $u['idUser'] ?>" onclick="return confirm('¿Borrar este usuario y todos sus datos asociados?')">Borrar</a>
                    </td>
                </tr>

                <dialog id="editar-<?= $u['idUser'] ?>" style="border:none;border-radius:8px;padding:0;max-width:560px;width:92%;">
                    <form method="POST" action="usuarios-administracion.php" class="tarjeta">
                        <input type="hidden" name="accion" value="editar">
                        <input type="hidden" name="idUser" value="<?= $u['idUser'] ?>">
                        <h3>Editar usuario: <?= htmlspecialchars($u['usuario'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <div class="fila-doble">
                            <div class="campo"><label>Nombre</label><input type="text" name="nombre" value="<?= htmlspecialchars($u['nombre'], ENT_QUOTES, 'UTF-8') ?>" required></div>
                            <div class="campo"><label>Apellidos</label><input type="text" name="apellidos" value="<?= htmlspecialchars($u['apellidos'], ENT_QUOTES, 'UTF-8') ?>" required></div>
                        </div>
                        <div class="campo"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?>" required></div>
                        <div class="fila-doble">
                            <div class="campo"><label>Teléfono</label><input type="text" name="telefono" value="<?= htmlspecialchars($u['telefono'], ENT_QUOTES, 'UTF-8') ?>" required></div>
                            <div class="campo"><label>Fecha de nacimiento</label><input type="date" name="fecha_nacimiento" value="<?= $u['fecha_nacimiento'] ?>" required></div>
                        </div>
                        <div class="campo"><label>Dirección</label><input type="text" name="direccion" value="<?= htmlspecialchars($u['direccion'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
                        <div class="fila-doble">
                            <div class="campo">
                                <label>Sexo</label>
                                <select name="sexo">
                                    <option value="Mujer" <?= $u['sexo']==='Mujer'?'selected':'' ?>>Mujer</option>
                                    <option value="Hombre" <?= $u['sexo']==='Hombre'?'selected':'' ?>>Hombre</option>
                                    <option value="Otro" <?= $u['sexo']==='Otro'?'selected':'' ?>>Otro</option>
                                </select>
                            </div>
                            <div class="campo">
                                <label>Rol</label>
                                <select name="rol">
                                    <option value="user" <?= $u['rol']==='user'?'selected':'' ?>>user</option>
                                    <option value="admin" <?= $u['rol']==='admin'?'selected':'' ?>>admin</option>
                                </select>
                            </div>
                        </div>
                        <div class="campo">
                            <label>Nueva contraseña</label>
                            <input type="password" name="password" minlength="6">
                            <small>Déjalo en blanco para no cambiarla.</small>
                        </div>
                        <button type="submit" class="boton">Guardar cambios</button>
                        <button type="button" class="boton boton-secundario" onclick="document.getElementById('editar-<?= $u['idUser'] ?>').close()">Cancelar</button>
                    </form>
                </dialog>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
</section>

<dialog id="crearUsuario" style="border:none;border-radius:8px;padding:0;max-width:560px;width:92%;">
    <form method="POST" action="usuarios-administracion.php" class="tarjeta">
        <input type="hidden" name="accion" value="crear">
        <h3>Nuevo usuario</h3>
        <div class="fila-doble">
            <div class="campo"><label>Nombre <span class="obligatorio">*</span></label><input type="text" name="nombre" required></div>
            <div class="campo"><label>Apellidos <span class="obligatorio">*</span></label><input type="text" name="apellidos" required></div>
        </div>
        <div class="campo"><label>Email <span class="obligatorio">*</span></label><input type="email" name="email" required></div>
        <div class="fila-doble">
            <div class="campo"><label>Teléfono <span class="obligatorio">*</span></label><input type="text" name="telefono" required></div>
            <div class="campo"><label>Fecha de nacimiento <span class="obligatorio">*</span></label><input type="date" name="fecha_nacimiento" required></div>
        </div>
        <div class="campo"><label>Dirección</label><input type="text" name="direccion"></div>
        <div class="fila-doble">
            <div class="campo">
                <label>Sexo</label>
                <select name="sexo">
                    <option value="Mujer">Mujer</option>
                    <option value="Hombre">Hombre</option>
                    <option value="Otro" selected>Otro</option>
                </select>
            </div>
            <div class="campo">
                <label>Rol <span class="obligatorio">*</span></label>
                <select name="rol" required>
                    <option value="user">user</option>
                    <option value="admin">admin</option>
                </select>
            </div>
        </div>
        <div class="fila-doble">
            <div class="campo"><label>Usuario <span class="obligatorio">*</span></label><input type="text" name="usuario" required></div>
            <div class="campo"><label>Contraseña <span class="obligatorio">*</span></label><input type="password" name="password" required minlength="6"></div>
        </div>
        <button type="submit" class="boton">Crear usuario</button>
        <button type="button" class="boton boton-secundario" onclick="document.getElementById('crearUsuario').close()">Cancelar</button>
    </form>
</dialog>

<?php include 'includes/footer.php'; ?>
</body>
</html>
