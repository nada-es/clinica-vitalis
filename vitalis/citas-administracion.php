<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';
$paginaActual = 'citas-administracion';

requiereRol('admin');
$errores = [];
$hoy = date('Y-m-d');

$idUserSeleccionado = isset($_GET['idUser']) ? (int) $_GET['idUser'] : (isset($_POST['idUser']) ? (int) $_POST['idUser'] : 0);

// --- Crear cita para el usuario seleccionado ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear') {
    $fecha_cita = limpiar($_POST['fecha_cita'] ?? '');
    $motivo_cita = limpiar($_POST['motivo_cita'] ?? '');

    if ($idUserSeleccionado <= 0) $errores[] = 'Selecciona un usuario.';
    if ($fecha_cita === '') $errores[] = 'La fecha es obligatoria.';

    if (empty($errores)) {
        $stmt = mysqli_prepare($conexion, "INSERT INTO citas (idUser, fecha_cita, motivo_cita) VALUES (?,?,?)");
        mysqli_stmt_bind_param($stmt, 'iss', $idUserSeleccionado, $fecha_cita, $motivo_cita);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        setMensaje('exito', 'Cita creada correctamente.');
        redirigir('citas-administracion.php?idUser=' . $idUserSeleccionado);
    }
}

// --- Editar cita ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'editar') {
    $idCita = (int) ($_POST['idCita'] ?? 0);
    $fecha_cita = limpiar($_POST['fecha_cita'] ?? '');
    $motivo_cita = limpiar($_POST['motivo_cita'] ?? '');

    if ($fecha_cita === '') {
        $errores[] = 'La fecha es obligatoria.';
    } else {
        $stmt = mysqli_prepare($conexion, "UPDATE citas SET fecha_cita=?, motivo_cita=? WHERE idCita=?");
        mysqli_stmt_bind_param($stmt, 'ssi', $fecha_cita, $motivo_cita, $idCita);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        setMensaje('exito', 'Cita actualizada correctamente.');
        redirigir('citas-administracion.php?idUser=' . $idUserSeleccionado);
    }
}

// --- Borrar cita ---
if (isset($_GET['borrar'])) {
    $idCita = (int) $_GET['borrar'];
    $stmt = mysqli_prepare($conexion, "DELETE FROM citas WHERE idCita=?");
    mysqli_stmt_bind_param($stmt, 'i', $idCita);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    setMensaje('exito', 'Cita eliminada correctamente.');
    redirigir('citas-administracion.php?idUser=' . $idUserSeleccionado);
}

$usuarios = mysqli_query($conexion,
    "SELECT d.idUser, d.nombre, d.apellidos, l.usuario
     FROM users_data d INNER JOIN users_login l ON l.idUser = d.idUser
     ORDER BY d.nombre ASC");

$citasUsuario = null;
$usuarioSeleccionadoNombre = '';
if ($idUserSeleccionado > 0) {
    $stmt = mysqli_prepare($conexion, "SELECT nombre, apellidos FROM users_data WHERE idUser=?");
    mysqli_stmt_bind_param($stmt, 'i', $idUserSeleccionado);
    mysqli_stmt_execute($stmt);
    $u = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if ($u) $usuarioSeleccionadoNombre = $u['nombre'] . ' ' . $u['apellidos'];

    $stmt = mysqli_prepare($conexion, "SELECT * FROM citas WHERE idUser=? ORDER BY fecha_cita ASC");
    mysqli_stmt_bind_param($stmt, 'i', $idUserSeleccionado);
    mysqli_stmt_execute($stmt);
    $citasUsuario = mysqli_stmt_get_result($stmt);
}
$meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="author" content="Nada Es Sabti">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de citas — Clínica Vitalis</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="pagina-cabecera">
    <div class="contenedor">
        <h1>Gestión de citas</h1>
        <p>Selecciona un paciente para ver, crear, modificar o borrar sus citas.</p>
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

        <form method="GET" action="citas-administracion.php" class="selector-usuario">
            <div class="campo">
                <label>Selecciona un paciente</label>
                <select name="idUser" onchange="this.form.submit()">
                    <option value="0">— Elige un paciente —</option>
                    <?php while ($u = mysqli_fetch_assoc($usuarios)): ?>
                        <option value="<?= $u['idUser'] ?>" <?= $idUserSeleccionado === (int) $u['idUser'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nombre'] . ' ' . $u['apellidos'] . ' (' . $u['usuario'] . ')', ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </form>

        <?php if ($idUserSeleccionado > 0 && $usuarioSeleccionadoNombre !== ''): ?>
        <div class="rejilla-2">
            <div>
                <h3>Citas de <?= htmlspecialchars($usuarioSeleccionadoNombre, ENT_QUOTES, 'UTF-8') ?></h3>
                <?php if (mysqli_num_rows($citasUsuario) === 0): ?>
                    <p>Este paciente no tiene citas registradas.</p>
                <?php else: ?>
                    <?php while ($c = mysqli_fetch_assoc($citasUsuario)):
                        $esPasada = $c['fecha_cita'] < $hoy;
                        $ts = strtotime($c['fecha_cita']);
                    ?>
                    <div class="ticket <?= $esPasada ? 'ticket-vencida' : '' ?>">
                        <div class="ticket-fecha">
                            <span class="dia"><?= date('d', $ts) ?></span>
                            <span class="mes"><?= $meses[(int) date('n', $ts) - 1] ?> <?= date('Y', $ts) ?></span>
                        </div>
                        <div class="ticket-perforacion"></div>
                        <div class="ticket-cuerpo">
                            <div>
                                <div class="motivo"><?= htmlspecialchars($c['motivo_cita'] ?: 'Sin motivo especificado', ENT_QUOTES, 'UTF-8') ?></div>
                                <?php if ($esPasada): ?><span class="etiqueta-vencida">Ya realizada</span><?php endif; ?>
                            </div>
                            <div class="ticket-acciones">
                                <button type="button" class="boton boton-secundario boton-pequeno" onclick="document.getElementById('editarCita-<?= $c['idCita'] ?>').showModal()">Editar</button>
                                <a href="citas-administracion.php?idUser=<?= $idUserSeleccionado ?>&borrar=<?= $c['idCita'] ?>" class="boton boton-peligro boton-pequeno" onclick="return confirm('¿Borrar esta cita?')">Borrar</a>
                            </div>
                        </div>
                    </div>

                    <dialog id="editarCita-<?= $c['idCita'] ?>" style="border:none;border-radius:8px;padding:0;max-width:480px;width:90%;">
                        <form method="POST" action="citas-administracion.php" class="tarjeta">
                            <input type="hidden" name="accion" value="editar">
                            <input type="hidden" name="idCita" value="<?= $c['idCita'] ?>">
                            <input type="hidden" name="idUser" value="<?= $idUserSeleccionado ?>">
                            <h3>Editar cita</h3>
                            <div class="campo"><label>Fecha</label><input type="date" name="fecha_cita" value="<?= $c['fecha_cita'] ?>" required></div>
                            <div class="campo"><label>Motivo</label><textarea name="motivo_cita" rows="3"><?= htmlspecialchars($c['motivo_cita'], ENT_QUOTES, 'UTF-8') ?></textarea></div>
                            <button type="submit" class="boton">Guardar</button>
                            <button type="button" class="boton boton-secundario" onclick="document.getElementById('editarCita-<?= $c['idCita'] ?>').close()">Cancelar</button>
                        </form>
                    </dialog>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>

            <div class="tarjeta">
                <h3>Nueva cita para este paciente</h3>
                <form method="POST" action="citas-administracion.php" class="formulario">
                    <input type="hidden" name="accion" value="crear">
                    <input type="hidden" name="idUser" value="<?= $idUserSeleccionado ?>">
                    <div class="campo"><label>Fecha <span class="obligatorio">*</span></label><input type="date" name="fecha_cita" required></div>
                    <div class="campo"><label>Motivo</label><textarea name="motivo_cita" rows="4"></textarea></div>
                    <button type="submit" class="boton">Crear cita</button>
                </form>
            </div>
        </div>
        <?php else: ?>
            <p>Selecciona un paciente en el desplegable para ver y gestionar sus citas.</p>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>
